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

class Survey extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_public',
        'is_active',
        'end_date',
        'auto_closed_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'end_date' => 'datetime',
        'auto_closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Survey $survey) {
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }
}
