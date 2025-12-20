<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\QuestionOptionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property string $question_id
 * @property string $option_text
 * @property int $order_index
 * @property-read Question $question
 * @property-read Collection<int, AnswerOption> $answerOptions
 */
final class QuestionOption extends Model
{
    /** @use HasFactory<QuestionOptionFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $table = 'question_options';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'order_index' => 'int',
        ];
    }

    /**
     * @return BelongsTo<Question, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * @return HasMany<AnswerOption, $this>
     */
    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }
}
