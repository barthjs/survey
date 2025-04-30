<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
