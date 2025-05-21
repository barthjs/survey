<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\UploadsCleanupJob;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'responses';

    public $timestamps = false;

    protected $fillable = [
        'survey_id',
        'ip_address',
        'user_agent',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Response $response) {
            $filesToDelete = [];

            $response->answers->each(function (Answer $answer) use (&$filesToDelete) {
                if (! empty($answer->file_path)) {
                    $filesToDelete[] = $answer->file_path;
                }
            });

            if (! empty($filesToDelete)) {
                UploadsCleanupJob::dispatch($filesToDelete);
            }
        });
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
