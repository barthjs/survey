<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnswerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $question_id
 * @property string $response_id
 * @property string|null $answer_text
 * @property string|null $file_path
 * @property string|null $original_file_name
 * @property-read Question $question
 * @property-read Response $response
 * @property-read Collection<int, AnswerOption> $selectedOptions
 */
final class Answer extends Model
{
    /** @use HasFactory<AnswerFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $table = 'answers';

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return BelongsTo<Response, $this>
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * @return HasMany<AnswerOption, $this>
     */
    public function selectedOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }

    protected static function booted(): void
    {
        self::deleted(function (self $answer): void {
            $response = $answer->response;

            // Delete the response if this was the last answer
            if ($response->answers()->count() === 0) {
                $response->delete();
            }
        });
    }
}
