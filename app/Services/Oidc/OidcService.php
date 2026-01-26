<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Two\User as SocialiteUser;
use RuntimeException;

final readonly class OidcService
{
    /**
     * @return array<array{icon: string, label: string}>
     */
    public function getEnabledProviders(): array
    {
        $enabledProviders = [];

        foreach (config()->array('services') as $provider => $config) {
            /** @phpstan-ignore-next-line */
            if (isset($config['oidc_enabled']) && $config['oidc_enabled']) {
                $enabledProviders[$provider] = [
                    'icon' => config()->string("services.$provider.icon"),
                    'label' => config()->string("services.$provider.label"),
                ];
            }
        }

        return $enabledProviders;
    }

    public function isEnabled(string $provider): bool
    {
        return config()->boolean("services.$provider.oidc_enabled");
    }

    /**
     * Creates a new user from an OIDC provider.
     *
     * @throws RuntimeException
     */
    public function handleCallback(string $provider, SocialiteUser $socialiteUser): User
    {
        $userProvider = UserProvider::findForProvider($provider, $socialiteUser);
        if ($userProvider !== null) {
            return $userProvider->user;
        }

        if (! config()->boolean('app.allow_registration')) {
            throw new RuntimeException('Registration is disabled');
        }

        $email = $socialiteUser->getEmail();
        if (empty($email)) {
            throw new RuntimeException('Provider did not return an email address');
        }

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => [
                    __('validation.unique', ['attribute' => 'email']),
                ],
            ]);
        }

        return DB::transaction(function () use ($provider, $socialiteUser): User {
            $user = $this->createUserFromSocialite($socialiteUser);
            UserProvider::createForProvider($provider, $socialiteUser, $user);

            return $user;
        });
    }

    /**
     * Links an OIDC provider to an existing user.
     */
    public function linkProvider(User $user, string $provider, SocialiteUser $socialiteUser): void
    {
        UserProvider::query()->updateOrCreate(
            ['provider_name' => $provider, 'provider_id' => $socialiteUser->getId()],
            ['user_id' => $user->id]
        );
    }

    private function createUserFromSocialite(SocialiteUser $socialiteUser): User
    {
        $email = $socialiteUser->getEmail();

        $name = $socialiteUser->getName()
            ?? $socialiteUser->getNickname()
            ?? $email;

        return User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'is_active' => true,
            'is_admin' => false,
        ]);
    }
}
