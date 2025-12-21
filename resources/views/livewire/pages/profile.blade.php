@php
    use Carbon\Carbon;
@endphp
<div class="max-w-4xl mx-auto space-y-8">
    <x-header :title="__('Profile')" :subtitle="__('Manage your profile and account settings')" separator/>

    <x-card :title="__('Profile')" :subtitle="__('Update your name and email address')">
        <x-form wire:submit="updateProfileInformation" novalidate autocomplete="off">
            <x-input icon="o-user" :label="__('Full name')" wire:model="name" required/>
            <x-input icon="o-at-symbol" :label="__('Email address')" wire:model="email" required/>

            <x-auth-session-status :status="session('new_email')" class="text-yellow-600"/>

            <x-slot:actions class="justify-start">
                <x-button
                    :label="__('Save')"
                    spinner="updateProfileInformation"
                    type="submit"
                    class="btn-primary"
                />
                @if (!empty($new_email) && ($new_email != $email))
                    <x-button
                        icon="o-envelope"
                        :label="__('Resend verification email')"
                        spinner="sendVerification"
                        wire:click="sendVerification"
                        class="btn-primary"
                        :disabled="$rateLimited"
                    />
                @endif
            </x-slot:actions>
        </x-form>
    </x-card>

    <x-card
        :title="__('Update password')"
        :subtitle="__('Ensure your account is using a long, random password to stay secure')"
    >
        <x-form wire:submit="updatePassword" novalidate autocomplete="off">
            <x-password
                :label="__('Current password')"
                clearable
                wire:model="current_password"
            />
            <x-password
                :label="__('New password')"
                clearable
                wire:model="password"
            />
            <x-password
                :label="__('Confirm password')"
                clearable
                wire:model="password_confirmation"
            />

            <x-slot:actions class="justify-start">
                <x-button
                    :label="__('Save')"
                    spinner="updatePassword"
                    type="submit"
                    class="btn-primary"
                />
            </x-slot:actions>
        </x-form>
    </x-card>

    <x-card
        :title="__('Devices & Sessions')"
        :subtitle="__('Manage your active sessions on other browsers and devices.')"
    >
        @if (count($this->sessions) > 0)
            <div class="mt-5 space-y-6">
                @foreach ($this->sessions as $session)
                    <div class="flex items-center">
                        <div>
                            @if ($session['device']['is_desktop'])
                                <x-icon
                                    name="o-computer-desktop"
                                    class="w-8 h-8 text-gray-500 dark:text-gray-400"
                                />
                            @else
                                <x-icon
                                    name="o-device-phone-mobile"
                                    class="w-8 h-8 text-gray-500 dark:text-gray-400"
                                />
                            @endif
                        </div>

                        <div class="ms-3">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $session['device']['platform'] ?: __('Unknown platform') }}
                                - {{ $session['device']['browser'] ?: __('Unknown browser') }}
                            </div>

                            <div>
                                <div class="text-xs text-gray-500">
                                    {{ $session['ip_address'] }},

                                    @if ($session['is_current_device'])
                                        <span class="font-semibold text-green-500 ">
                                            {{ __('This device') }}
                                        </span>
                                    @else
                                        {{ __('Last active') }} {{ Carbon::createFromTimestamp($session['last_active'])->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <x-button
                    :label="__('Log Out Other Sessions')"
                    wire:click="openConfirmLogoutOtherBrowserSessionsModal"
                    class="btn-error"
                />
            </div>
        @endif

        <x-modal
            :title="__('Log Out Other Sessions')"
            :subtitle="__('Please enter your password to log out your other sessions on all devices.')"
            wire:model="confirmLogoutOtherBrowserSessionsModal"
            class="backdrop-blur"
        >
            <x-form wire:submit="logoutOtherBrowserSessions" novalidate>
                <x-password :label="__('Password')" wire:model="confirm_logout_password"/>

                <x-slot:actions class="justify-start">
                    <x-button
                        icon="o-x-circle"
                        :label="__('Cancel')"
                        wire:click="closeConfirmLogoutOtherBrowserSessionsModal"
                        class="btn-secondary"
                    />
                    <x-button
                        icon="o-power"
                        :label="__('Confirm')"
                        spinner="logoutOtherBrowserSessions"
                        type="submit"
                        class="btn-primary"
                    />
                </x-slot:actions>
            </x-form>
        </x-modal>
    </x-card>

    <x-card :title="__('Delete account')" :subtitle="__('Delete your account and all of its resources')">
        <x-modal
            :title="__('Are you sure you want to delete your account?')"
            :subtitle="__('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.')"
            wire:model="confirmUserDeletionModal"
            class="backdrop-blur"
        >
            <x-form wire:submit="deleteUser" novalidate autocomplete="off">
                <x-password :label="__('Password')" wire:model="confirm_delete_password" required/>

                <x-slot:actions class="justify-start">
                    <x-button
                        icon="o-x-circle"
                        :label="__('Cancel')"
                        wire:click="closeConfirmUserDeletionModal"
                        class="btn-secondary"
                    />
                    <x-button
                        icon="o-trash"
                        :label="__('Delete account')"
                        type="submit"
                        class="btn-error"
                    />
                </x-slot:actions>
            </x-form>
        </x-modal>

        <x-button
            icon="o-trash"
            :label="__('Delete account')"
            wire:click="openConfirmUserDeletionModal"
            class="btn-error"
        />
    </x-card>
</div>
