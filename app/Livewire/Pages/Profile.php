<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Actions\Logout;
use App\Models\User;
use App\Notifications\VerifyNewEmailNotification;
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

    /** @var array<int, array<string, mixed>> */
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

        $this->sessions = $this->getSessions();
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
        try {
            /** @var array{current_password: string, password: string} $validated */
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->success(__('Password updated'));
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
        $this->validate(
            rules: [
                'confirm_logout_password' => ['required', 'string', 'current_password'],
            ],
            attributes: [
                'confirm_logout_password' => __('validation.attributes.current_password'),
            ]
        );

        $user = auth()->user();

        Auth::logoutOtherDevices($this->confirm_logout_password);

        request()->session()->put([
            'password_hash_'.Auth::getDefaultDriver() => $user->password,
        ]);

        DB::table(config()->string('session.table'))
            ->where('user_id', $user->id)
            ->where('id', '!=', request()->session()->getId())
            ->delete();

        $this->mount();

        $this->confirmLogoutOtherBrowserSessionsModal = false;
        $this->success(__('All other browser sessions have been logged out successfully.'));
    }

    public function closeConfirmLogoutOtherBrowserSessionsModal(): void
    {
        $this->confirmLogoutOtherBrowserSessionsModal = false;
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate(
            rules: [
                'confirm_delete_password' => ['required', 'string', 'current_password'],
            ],
            attributes: [
                'confirm_delete_password' => __('validation.attributes.current_password'),
            ]
        );

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect(route('home'), navigate: true);
    }

    public function openConfirmUserDeletionModal(): void
    {
        $this->resetErrorBag('confirm_delete_password');
        $this->reset('confirm_delete_password');
        $this->confirmUserDeletionModal = true;
    }

    public function closeConfirmUserDeletionModal(): void
    {
        $this->confirmUserDeletionModal = false;
    }

    public function render(): Factory|View
    {
        return view('livewire.pages.profile')
            ->title(__('Profile'));
    }

    /**
     * @return array<int, array{
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
    private function getSessions(): array
    {
        /** @var Collection<int, object{ id: string, user_agent: string|null, ip_address: string|null, last_activity: int }> $sessions */
        $sessions = DB::table('sys_sessions')
            ->where('user_id', '=', auth()->user()->id)
            ->latest('last_activity')
            ->get();

        $result = [];
        $agent = new Agent();
        foreach ($sessions as $session) {
            $agent->setUserAgent($session->user_agent);

            $result[] = [
                'device' => [
                    'is_desktop' => $agent->isDesktop(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                ],
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => $session->last_activity,
            ];
        }

        return $result;
    }
}
