<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Actions\Logout;
use App\Models\User;
use App\Notifications\VerifyNewEmailNotification;
use App\Services\Oidc\OidcService;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Jenssegers\Agent\Agent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.app')]
final class Profile extends Component
{
    use Toast;

    public string $name = '';

    public string $email = '';

    public string $new_email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $confirmTwoFactorAuthenticationModal = false;

    public string $confirm_2fa_password = '';

    #[Locked]
    public bool $showingTwoFactorQrCode = false;

    public string $two_factor_code = '';

    public bool $showingRecoveryCodes = false;

    /** @var array<int, string> */
    public array $recovery_codes = [];

    public bool $confirmRegenerateRecoveryCodesModal = false;

    public bool $confirmDisableTwoFactorAuthenticationModal = false;

    /** @var array<array{label: string, is_connected: bool, id?: int|string|null}> */
    public array $oidcProviders = [];

    public bool $confirmRemoveProviderModal = false;

    public ?string $selectedProviderId = null;

    /**
     * @var array<int, array{
     *     device: array{
     *         is_desktop: bool,
     *         platform: bool|string,
     *         browser: bool|string
     *     },
     *     ip_address: string|null,
     *     is_current_device: bool,
     *     last_active: int
     * }>
     */
    public array $sessions = [];

    public bool $confirmLogoutOtherBrowserSessionsModal = false;

    public string $confirm_logout_password = '';

    public bool $confirmUserDeletionModal = false;

    public string $confirm_delete_password = '';

    public bool $rateLimited = false;

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->new_email = Auth::user()->new_email ?? '';

        $this->loadOidcProviders();
        $this->loadSessions();
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $this->email = mb_strtolower($this->email);

        /** @var array{name: string, email: string} $validated */
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->name = $validated['name'];
        $validated['email'] = mb_strtolower($validated['email']);

        if ($user->email !== $validated['email']) {
            if (config()->boolean('app.enable_email_verification')) {
                $user->new_email = $validated['email'];
                $this->sendVerification();
            } else {
                $user->email = $validated['email'];
            }
        }

        $user->save();

        $this->success(__('Profile information updated'));
    }

    public function sendVerification(): void
    {
        if (! config()->boolean('app.enable_email_verification')) {
            return;
        }

        $user = Auth::user();
        if (empty($user->new_email)) {
            $this->error(__('Email already verified.'));

            return;
        }

        $key = 'send-new-verification-email:'.$user->email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->rateLimited = true;
            $this->error(__('Too many verification requests. Please try again later.'));

            return;
        }

        RateLimiter::hit($key);

        Notification::route('mail', $user->new_email)->notify(new VerifyNewEmailNotification($user->id, $user->new_email)->locale(app()->getLocale()));

        $this->success(__('Verification email sent. Please check your email.'));
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ];

        if ($user->password !== null) {
            $rules['current_password'] = ['required', 'string', 'current_password'];
        }

        try {
            /** @var array{current_password?: string, password: string} $validated */
            $validated = $this->validate($rules);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->loadOidcProviders();

        $this->success(__('Password updated'));
    }

    public function getTwoFactorQrCodeSvgProperty(TwoFactorAuthenticationService $service): string
    {
        $user = Auth::user();
        if (empty($user->two_factor_secret)) {
            return '';
        }

        return $service->getQRCodeSvg(
            config()->string('app.name'),
            $user->email,
            $user->two_factor_secret
        );
    }

    public function openConfirmTwoFactorAuthenticationModal(): void
    {
        $this->reset('confirm_2fa_password');
        $this->resetErrorBag('confirm_2fa_password');

        $this->confirmTwoFactorAuthenticationModal = true;
    }

    public function enableTwoFactorAuthentication(TwoFactorAuthenticationService $service): void
    {
        $this->validate(
            rules: [
                'confirm_2fa_password' => ['required', 'string', 'current_password'],
            ],
            attributes: [
                'confirm_2fa_password' => __('validation.attributes.current_password'),
            ]);

        $user = Auth::user();
        $user->two_factor_secret = $service->generateSecretKey();
        $user->two_factor_recovery_codes = [];
        $user->save();

        $this->showingTwoFactorQrCode = true;

        $this->confirmTwoFactorAuthenticationModal = false;
    }

    public function confirmTwoFactorAuthentication(TwoFactorAuthenticationService $service): void
    {
        $user = Auth::user();
        if (! $service->verify($user->two_factor_secret, $this->two_factor_code)) {
            throw ValidationException::withMessages([
                'two_factor_code' => [__('The provided two factor authentication code was invalid.')],
            ]);
        }

        $this->recovery_codes = $service->generateRecoveryCodes();

        $user->two_factor_recovery_codes = $service->hashRecoveryCodes($this->recovery_codes);
        $user->two_factor_enabled_at = now();
        $user->save();

        $this->showingTwoFactorQrCode = false;
        $this->showingRecoveryCodes = true;

        $this->two_factor_code = '';

        $this->success(__('Two factor authentication enabled.'));
    }

    public function openConfirmRegenerateRecoveryCodesModal(): void
    {
        $this->reset('confirm_2fa_password');
        $this->resetErrorBag('confirm_2fa_password');

        $this->confirmRegenerateRecoveryCodesModal = true;
    }

    public function regenerateRecoveryCodes(TwoFactorAuthenticationService $service): void
    {
        $this->validate(
            rules: [
                'confirm_2fa_password' => ['required', 'string', 'current_password'],
            ],
            attributes: [
                'confirm_2fa_password' => __('validation.attributes.current_password'),
            ]);

        $user = Auth::user();

        $this->recovery_codes = $service->generateRecoveryCodes();

        $user->two_factor_recovery_codes = $service->hashRecoveryCodes($this->recovery_codes);
        $user->save();

        $this->showingRecoveryCodes = true;

        $this->confirmRegenerateRecoveryCodesModal = false;
        $this->success(__('Recovery codes regenerated.'));
    }

    public function openConfirmDisableTwoFactorAuthenticationModal(): void
    {
        $this->reset('confirm_2fa_password');
        $this->resetErrorBag('confirm_2fa_password');

        $this->confirmDisableTwoFactorAuthenticationModal = true;
    }

    public function disableTwoFactorAuthentication(): void
    {
        $this->validate(
            rules: [
                'confirm_2fa_password' => ['required', 'string', 'current_password'],
            ],
            attributes: [
                'confirm_2fa_password' => __('validation.attributes.current_password'),
            ]);

        $user = Auth::user();

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_enabled_at = null;
        $user->save();

        $this->confirmDisableTwoFactorAuthenticationModal = false;
        $this->success(__('Two factor authentication disabled.'));
    }

    public function openConfirmRemoveProviderModal(string $id): void
    {
        $this->selectedProviderId = $id;
        $this->confirmRemoveProviderModal = true;
    }

    public function removeProvider(): void
    {
        $user = Auth::user();
        if ($user->password === null && $user->providers()->count() <= 1) {
            return;
        }

        $user->providers()->where('id', $this->selectedProviderId)->delete();
        $this->loadOidcProviders();

        $this->confirmRemoveProviderModal = false;
        $this->success(__('The connection has been successfully removed.'));
    }

    public function openConfirmLogoutOtherBrowserSessionsModal(): void
    {
        $this->reset('confirm_logout_password');
        $this->resetErrorBag('confirm_logout_password');

        $this->confirmLogoutOtherBrowserSessionsModal = true;
    }

    /**
     * Log out from other browser sessions.
     */
    public function logoutOtherBrowserSessions(): void
    {
        $user = Auth::user();
        if ($user->password !== null) {
            $this->validate([
                'confirm_logout_password' => ['required', 'string', 'current_password'],
            ]);

            Auth::logoutOtherDevices($this->confirm_logout_password);

            request()->session()->put([
                'password_hash_'.Auth::getDefaultDriver() => $user->password,
            ]);
        }

        DB::table(config()->string('session.table'))
            ->where('user_id', $user->id)
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        $this->loadSessions();

        $this->confirmLogoutOtherBrowserSessionsModal = false;
        $this->success(__('All other sessions have been logged out successfully.'));
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $user = Auth::user();

        if ($user->password !== null) {
            $this->validate([
                'confirm_delete_password' => ['required', 'string', 'current_password'],
            ]);
        }

        tap($user, $logout(...))->delete();

        $this->redirect(route('home'), navigate: true);
    }

    public function openConfirmUserDeletionModal(): void
    {
        $this->resetErrorBag('confirm_delete_password');
        $this->reset('confirm_delete_password');

        $this->confirmUserDeletionModal = true;
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.profile')
            ->title(__('Profile'));
    }

    private function loadOidcProviders(): void
    {
        $service = app(OidcService::class);
        $enabled = $service->getEnabledProviders();

        $user = Auth::user();
        $connected = $user->providers;
        $connectedCount = $connected->count();
        $hasPassword = $user->password !== null;

        $this->oidcProviders = [];
        foreach ($enabled as $slug => $data) {
            $connection = $connected->firstWhere('provider_name', $slug);
            $this->oidcProviders[$slug] = [
                'icon' => $data['icon'],
                'label' => $data['label'],
                'is_connected' => $connection !== null,
                'can_be_removed' => $hasPassword || $connectedCount > 1,
                'id' => $connection?->id,
            ];
        }
    }

    private function loadSessions(): void
    {
        /** @var Collection<int, object{ id: string, user_agent: string|null, ip_address: string|null, last_activity: int }> $sessions */
        $sessions = DB::table(config()->string('session.table'))
            ->where('user_id', '=', auth()->user()->id)
            ->latest('last_activity')
            ->get();

        $currentSessionId = request()->hasSession() ? request()->session()->getId() : null;

        $agent = new Agent();
        $this->sessions = [];
        foreach ($sessions as $session) {
            $agent->setUserAgent($session->user_agent);

            $this->sessions[] = [
                'device' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $currentSessionId,
                'last_active' => $session->last_activity,
            ];
        }
    }
}
