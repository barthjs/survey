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
use Illuminate\Support\Facades\Hash;

/**
 * @property-read string $id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property string $name
 * @property string $email
 * @property string|null $new_email
 * @property CarbonInterface|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property bool $is_active
 * @property bool $is_admin
 * @property string|null $two_factor_secret
 * @property array<string>|null $two_factor_recovery_codes
 * @property CarbonInterface|null $two_factor_enabled_at
 * @property-read string $initials
 * @property-read Collection<int, UserProvider> $providers
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
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_enabled_at' => 'datetime',
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
     * @return HasMany<UserProvider, $this>
     */
    public function providers(): HasMany
    {
        return $this->hasMany(UserProvider::class, 'user_id');
    }

    /**
     * @return HasMany<Survey, $this>
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Consume a recovery code for the user.
     */
    public function consumeRecoveryCode(string $recoveryCode): bool
    {
        $codes = $this->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $hashed) {
            if (Hash::check($recoveryCode, $hashed)) {
                unset($codes[$index]);
                $this->two_factor_recovery_codes = array_values($codes);
                $this->save();

                return true;
            }
        }

        return false;
    }

    protected static function booted(): void
    {
        self::deleting(function (self $user): void {
            $filesToDelete = [];

            $user->load([
                'surveys.questions.answers',
            ]);

            $user->surveys->each(function (Survey $survey) use (&$filesToDelete): void {
                $survey->questions->each(function (Question $question) use (&$filesToDelete): void {
                    if ($question->type === QuestionType::FILE) {
                        $question->answers->each(function (Answer $answer) use (&$filesToDelete): void {
                            if (! empty($answer->file_path)) {
                                $filesToDelete[] = $answer->file_path;
                            }
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
