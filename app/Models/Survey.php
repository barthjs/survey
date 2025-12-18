<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Jobs\UploadsCleanupJob;
use Carbon\CarbonInterface;
use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property string $user_id
 * @property string $title
 * @property string|null $description
 * @property bool $is_public
 * @property bool $is_active
 * @property CarbonInterface|null $end_date
 * @property CarbonInterface|null $auto_closed_at
 * @property-read User $user
 * @property-read Collection<int, Question> $questions
 * @property-read Collection<int, Response> $responses
 */
final class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory, HasUlids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string|bool>
     */
    protected $attributes = [
        'is_public' => false,
        'is_active' => true,
    ];

    public static function getValidationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'end_date' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'end_date' => 'datetime',
            'auto_closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Question, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * @return HasMany<Response, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    protected static function booted(): void
    {
        self::deleting(function (Survey $survey) {
            $filesToDelete = [];

            $survey->questions->each(function (Question $question) use (&$filesToDelete) {
                if ($question->type === QuestionType::FILE) {
                    $question->answers()->each(function (Answer $answer) use (&$filesToDelete) {
                        $filesToDelete[] = $answer->file_path;
                    });
                }
            });

            if (! empty($filesToDelete)) {
                UploadsCleanupJob::dispatch($filesToDelete);
            }
        });
    }
}
