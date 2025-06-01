<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Jobs\UploadsCleanupJob;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Question extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'questions';

    public $timestamps = false;

    protected $fillable = [
        'survey_id',
        'question_text',
        'type',
        'is_required',
        'order_index',
    ];

    protected $casts = [
        'type' => QuestionType::class,
        'is_required' => 'bool',
        'order_index' => 'int',
    ];

    protected static array $responseIdsToCheck = [];

    protected static function booted(): void
    {
        static::updated(function (Question $question) {
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

        static::deleting(function (Question $question) {
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

        static::deleted(function () {
            foreach (self::$responseIdsToCheck as $responseId) {
                if (Answer::where('response_id', '=', $responseId)->count() === 0) {
                    Response::find($responseId)?->delete();
                }
            }

            self::$responseIdsToCheck = [];
        });
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public static function validateQuestions(array $questions): void
    {
        Validator::make(['questions' => $questions], [
            'questions' => ['required', 'array', 'max:100'],
            'questions.*.question_text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', Rule::enum(QuestionType::class)],
            'questions.*.is_required' => ['required', 'boolean'],
            'questions.*.options' => ['nullable', 'array', 'max:10'],
            'questions.*.options.*.option_text' => ['required_with:questions.*.options', 'string', 'max:255'],
        ])->after(function ($validator) use ($questions) {
            $hasRequired = collect($questions)->contains(fn ($q) => $q['is_required']);
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
}
