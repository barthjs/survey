@props([
    'mode'
])
@if(!empty($availableProviders))
    <div {{ $attributes->merge(['class' => 'flex flex-col items-center space-y-4 mt-4']) }}>
        <div class="flex items-center w-full">
            <div class="grow border-t border-gray-300 dark:border-gray-700"></div>
            <span class="px-3 text-gray-500">
                @if($mode === 'login')
                    {{ __('or sign in with') }}
                @else
                    {{ __('or sign up with') }}
                @endif
        </span>
            <div class="grow border-t border-gray-300 dark:border-gray-700"></div>
        </div>

        <div class="grid @if(count($availableProviders) > 1) md:grid-cols-2 @endif gap-4 w-full">
            @foreach ($availableProviders as $key => $provider)
                <x-button
                    :icon="$provider['icon']"
                    :label="$provider['label']"
                    :link="route('auth.oidc.redirect', ['provider' => $key])"
                    noWireNavigate
                    @class([
                        'w-full',
                        'btn-outline',
                        'md:col-span-2' => $loop->last && count($availableProviders) % 2 !== 0 && count($availableProviders) > 1,
                    ])
                >
                </x-button>
            @endforeach
        </div>
    </div>
@endif
