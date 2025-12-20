<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\UploadsCleanupJob;
use Carbon\CarbonInterface;
use Database\Factories\ResponseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $survey_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property CarbonInterface $submitted_at
 * @property-read Survey $survey
 * @property-read Collection<int, Answer> $answers
 */
final class Response extends Model
{
    /** @use HasFactory<ResponseFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $table = 'responses';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string|bool>
     */
    protected $attributes = [
        'submitted_at' => 'now',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
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
     * @return HasMany<Answer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    protected static function booted(): void
    {
        self::deleting(function (self $response): void {
            $filesToDelete = [];

            $response->answers->each(function (Answer $answer) use (&$filesToDelete): void {
                if (! empty($answer->file_path)) {
                    $filesToDelete[] = $answer->file_path;
                }
            });

            if (! empty($filesToDelete)) {
                UploadsCleanupJob::dispatch($filesToDelete);
            }
        });
    }
}
