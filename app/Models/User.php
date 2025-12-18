<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuestionType;
use App\Jobs\UploadsCleanupJob;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property-read string $id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property string $name
 * @property string $email
 * @property string|null $new_email
 * @property string $password
 * @property string|null $remember_token
 * @property bool $is_active
 * @property bool $is_admin
 * @property-read string $initials
 * @property-read Collection<int, Survey> $surveys
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable;

    protected $table = 'sys_users';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, string|bool>
     */
    protected $attributes = [
        'is_active' => true,
        'is_admin' => false,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get user initials from the username
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            }
        }

        return $initials;
    }

    /**
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    protected static function booted(): void
    {
        self::deleting(function (User $user): void {
            $filesToDelete = [];

            $user->load([
                'surveys.questions.answers',
            ]);

            $user->surveys->each(function (Survey $survey) use (&$filesToDelete) {
                $survey->questions->each(function (Question $question) use (&$filesToDelete) {
                    if ($question->type === QuestionType::FILE) {
                        $question->answers->each(function (Answer $answer) use (&$filesToDelete) {
                            $filesToDelete[] = $answer->file_path;
                        });
                    }
                });
            });

            if (! empty($filesToDelete)) {
                UploadsCleanupJob::dispatch($filesToDelete);
            }
        });
    }
}
