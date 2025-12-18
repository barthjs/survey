<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnswerOptionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property string $answer_id
 * @property string $question_option_id
 * @property-read Answer $answer
 * @property-read QuestionOption $option
 */
final class AnswerOption extends Model
{
    /** @use HasFactory<AnswerOptionFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $table = 'answer_options';

    /**
     * @return BelongsTo<Answer, $this>
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    /**
     * @return BelongsTo<QuestionOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
