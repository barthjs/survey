<x-card
    shadow
    class="w-full max-w-lg mx-auto"
>
    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status
            :status="__('A new verification link has been sent to the email address you provided during registration.')"
            class="text-center"
        />
    @else
        <x-auth-session-status :status="session('status')" class="text-center text-red-600"/>
    @endif

    <p class="py-4">{{ __('Please verify your email address by clicking on the link we just emailed to you.') }}<p>

    <hr class="my-6">

    <div class="flex flex-col sm:flex-row justify-end gap-3">
        <x-button
            icon="o-envelope"
            :label="__('Resend verification email')"
            spinner="sendVerification"
            wire:click="sendVerification"
            class="btn-primary"
            :disabled="$rateLimited"
        />
        <x-button
            icon="o-power"
            :label="__('Log out')"
            wire:click="logout"
        />
    </div>

    <script>
        const verificationRoute = @json(route('verification.verified'));
        const redirectRoute = @json(route('surveys.index'));

        // Check if the user is verified every 15 seconds.
        // If the user is verified, redirect to the dashboard.
        const pollVerification = () => {
            fetch(verificationRoute, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.verified) {
                        if (window.Livewire?.navigate) {
                            Livewire.navigate(redirectRoute);
                        } else {
                            window.location.href = redirectRoute;
                        }
                    }
                })
                .catch(() => {
                    console.log('Error checking verification status.');
                });
        };

        setInterval(pollVerification, 15000);
    </script>
</x-card>

