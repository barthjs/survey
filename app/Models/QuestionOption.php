<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'question_options';

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'option_text',
        'order_index',
    ];

    protected $casts = [
        'order_index' => 'int',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answerOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }
}
