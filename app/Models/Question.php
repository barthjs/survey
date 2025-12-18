<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Jobs\UploadsCleanupJob;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @property-read string $id
 * @property string $survey_id
 * @property string $question_text
 * @property QuestionType $type
 * @property bool $is_required
 * @property int $order_index
 * @property-read Survey $survey
 * @property-read Collection<int, QuestionOption> $options
 * @property-read Collection<int, Answer> $answers
 */
final class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $table = 'questions';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string|bool>
     */
    protected $attributes = [
        'is_required' => false,
    ];

    protected static array $responseIdsToCheck = [];

    public static function validateQuestions(array $questions): void
    {
        Validator::make(['questions' => $questions], [
            'questions' => ['required', 'array', 'max:100'],
            'questions.*.question_text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.is_required' => ['required', 'boolean'],
            'questions.*.options' => ['nullable', 'array', 'max:10'],
            'questions.*.options.*.option_text' => ['required_with:questions.*.options', 'string', 'max:255'],
        ])->after(function (\Illuminate\Validation\Validator $validator) use ($questions) {
            $hasRequired = collect($questions)->contains(fn (array $q) => $q['is_required']);
            if (! $hasRequired) {
                $validator->errors()->add('questions', __('At least one question must be marked as required.'));
            }

            foreach ($questions as $question) {
                if (
                    $question['type'] === QuestionType::MULTIPLE_CHOICE->name &&
                    (! isset($question['options']) || count($question['options']) < 2)
                ) {
                    $validator->errors()->add('questions', '');
                }
            }
        })->validate();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'is_required' => 'bool',
            'order_index' => 'int',
        ];
    }

    /**
     * @return BelongsTo<Survey, $this>
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * @return HasMany<QuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * @return HasMany<Answer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    protected static function booted(): void
    {
        self::updated(function (Question $question) {
            if ($question->isDirty('type')) {
                $originalType = $question->getOriginal('type');
                $newType = $question->type;

                if ($originalType === QuestionType::TEXT && $newType !== QuestionType::TEXT) {
                    $question->answers()->each(function (Answer $answer) {
                        $answer->delete();
                    });
                }

                if ($originalType === QuestionType::MULTIPLE_CHOICE && $newType !== QuestionType::MULTIPLE_CHOICE) {
                    $question->answers()->each(function (Answer $answer) {
                        $answer->delete();
                    });

                    $question->options()->each(function (QuestionOption $option) {
                        $option->delete();
                    });
                }

                if ($originalType === QuestionType::FILE && $newType !== QuestionType::FILE) {
                    $filesToDelete = [];

                    $question->answers()->each(function (Answer $answer) use (&$filesToDelete) {
                        $filesToDelete[] = $answer->file_path;
                        $answer->delete();
                    });

                    UploadsCleanupJob::dispatch($filesToDelete);
                }
            }
        });

        self::deleting(function (Question $question) {
            $filesToDelete = [];

            if ($question->type === QuestionType::FILE) {
                $question->answers()->each(function (Answer $answer) use (&$filesToDelete) {
                    $filesToDelete[] = $answer->file_path;
                });

                UploadsCleanupJob::dispatch($filesToDelete);
            }

            // Collect response IDs before answers get deleted
            self::$responseIdsToCheck = $question->answers()->pluck('response_id')->unique()->toArray();
        });

        self::deleted(function () {
            foreach (self::$responseIdsToCheck as $responseId) {
                if (Answer::where('response_id', '=', $responseId)->count() === 0) {
                    Response::find($responseId)?->delete();
                }
            }

            self::$responseIdsToCheck = [];
        });
    }
}
