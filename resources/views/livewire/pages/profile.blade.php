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
        :title="__('Two factor authentication')"
        :subtitle="__('Add additional security to your account using two factor authentication.')"
    >
        @if(auth()->user()->two_factor_enabled_at)
            <x-alert
                icon="o-check-circle"
                :title="__('You have enabled two factor authentication.')"
                class="alert-success"
            />

            @if($showingRecoveryCodes)
                <div x-data="{ copied: false }" class="mt-6 space-y-4">
                    <x-alert
                        icon="o-key"
                        :title="__('Recovery codes')"
                        :description="__('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.')"
                        class="alert-warning"
                    />

                    <ul class="list-disc list-inside text-center grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono text-sm bg-base-200 p-4 rounded-lg">
                        @foreach ($recovery_codes as $code)
                            <li class="tracking-wider">{{ $code }}</li>
                        @endforeach
                    </ul>

                    <x-button
                        icon="o-clipboard"
                        :label="__('Copy to clipboard')"
                        @click="
                            navigator.clipboard.writeText('{{ implode('\n', $recovery_codes) }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="btn-outline"
                    />

                    <p x-show="copied" class="text-green-600">
                        {{ __('Recovery codes copied!') }}
                    </p>
                </div>
            @endif

            <div class="mt-6 flex gap-4">
                @if (!$showingRecoveryCodes)
                    <x-button
                        :label="__('Regenerate Recovery Codes')"
                        wire:click="openConfirmRegenerateRecoveryCodesModal()"
                        class="btn-secondary"
                    />
                @endif

                <x-button
                    :label="__('Disable')"
                    wire:click="openConfirmDisableTwoFactorAuthenticationModal()"
                    class="btn-error"
                />
            </div>

            <x-modal
                wire:model="confirmRegenerateRecoveryCodesModal"
                :title="__('Regenerate Recovery Codes')"
                :subtitle="__('Are you sure you want to regenerate your recovery codes? Once you regenerate your recovery codes, the old ones will no longer work.')"
                class="backdrop-blur"
            >
                <x-form wire:submit="regenerateRecoveryCodes" novalidate>
                    <x-password
                        :label="__('Current password')"
                        wire:model="confirm_2fa_password"
                        autocomplete="current-password"
                    />

                    <x-slot:actions class="justify-start">
                        <x-button
                            icon="o-x-circle"
                            :label="__('Cancel')"
                            @click="$wire.confirmRegenerateRecoveryCodesModal = false"
                            class="btn-secondary"
                        />
                        <x-button
                            :label="__('Regenerate')"
                            spinner="regenerateRecoveryCodes"
                            type="submit"
                            class="btn-primary"
                        />
                    </x-slot:actions>
                </x-form>
            </x-modal>

            <x-modal
                wire:model="confirmDisableTwoFactorAuthenticationModal"
                :title="__('Disable Two Factor Authentication')"
                :subtitle="__('Are you sure you would like to disable two factor authentication?')"
                class="backdrop-blur"
            >
                <x-form wire:submit="disableTwoFactorAuthentication">
                    <x-password
                        :label="__('Current password')"
                        wire:model="confirm_2fa_password"
                        autocomplete="current-password"
                    />

                    <x-slot:actions class="justify-start">
                        <x-button
                            icon="o-x-circle"
                            :label="__('Cancel')"
                            @click="$wire.confirmDisableTwoFactorAuthenticationModal = false"
                            class="btn-secondary"
                        />
                        <x-button
                            :label="__('Disable')"
                            spinner="disableTwoFactorAuthentication"
                            type="submit"
                            class="btn-error"
                        />
                    </x-slot:actions>
                </x-form>
            </x-modal>
        @else
            <x-alert
                icon="o-shield-check"
                :title="__('You have not enabled two factor authentication.')"
                :description="__('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s authenticator application.')"
            />

            @if ($showingTwoFactorQrCode)
                <div class="mt-6 space-y-6">
                    <x-alert
                        icon="o-information-circle"
                        :title="__('Finalize enabling 2FA')"
                        :description="__('Scan the following QR code using your phone\'s authenticator application to enable two factor authentication.') "
                        class="alert-info"
                    />

                    <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
                        <div class="bg-white p-2 rounded-lg">
                            {!! $this->twoFactorQrCodeSvg !!}
                        </div>

                        <div>
                            <div>
                                <p class="text-sm font-bold">{{ __('Setup Key') }}</p>
                                <p class="font-mono text-lg tracking-widest break-all">{{ auth()->user()->two_factor_secret }}</p>
                            </div>

                            <x-form wire:submit="confirmTwoFactorAuthentication" novalidate>
                                <x-input
                                    :label="__('Verification Code')"
                                    wire:model="two_factor_code"
                                    autocomplete="one-time-code"
                                    class="max-w-xs"
                                    placeholder="000000"
                                />

                                <x-slot:actions class="justify-start">
                                    <x-button
                                        :label="__('Confirm')"
                                        type="submit"
                                        spinner="confirmTwoFactorAuthentication"
                                        class="btn-primary"
                                    />
                                </x-slot:actions>
                            </x-form>
                        </div>
                    </div>
                </div>
            @else
                <x-slot:menu>
                    <x-button
                        :label="__('Enable')"
                        wire:click="openConfirmTwoFactorAuthenticationModal()"
                        class="btn-primary"
                    />
                </x-slot:menu>

                <x-modal
                    wire:model="confirmTwoFactorAuthenticationModal"
                    :title="__('Enable Two Factor Authentication')"
                    :subtitle="__('Please enter your password to confirm you would like to enable two factor authentication.')"
                    class="backdrop-blur"
                >
                    <x-form wire:submit="enableTwoFactorAuthentication" novalidate>
                        <x-password :label="__('Current password')" wire:model="confirm_2fa_password"/>

                        <x-slot:actions class="justify-start">
                            <x-button
                                icon="o-x-circle"
                                :label="__('Cancel')"
                                @click="$wire.confirmTwoFactorAuthenticationModal = false"
                                class="btn-secondary"
                            />
                            <x-button
                                :label="__('Confirm')"
                                spinner="enableTwoFactorAuthentication"
                                type="submit"
                                class="btn-primary"
                            />
                        </x-slot:actions>
                    </x-form>
                </x-modal>
            @endif
        @endif
    </x-card>

    <x-card
        :title="__('Devices & Sessions')"
        :subtitle="__('Manage your active sessions on other browsers and devices.')"
    >
        @if (count($this->sessions) > 0)
            <div class="space-y-6">
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
