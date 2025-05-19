<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Answer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'answers';

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'response_id',
        'answer_text',
        'file_path',
        'original_file_name',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function selectedOptions(): HasMany
    {
        return $this->hasMany(AnswerOption::class);
    }
}
