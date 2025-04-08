<x-layouts.app>
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center p-8">
            <h1 class="text-3xl">{{ config('app.name') }}</h1>
            <p>{{ config('app.version') }}</p>
            <a href="{{ route('login') }}" class="font-medium underline px-3">{{ __('Log in ')}}</a>
            <a href="{{ route('register') }}" class="font-medium underline px-3">{{ __('Register')}}</a>
        </div>
    </div>
</x-layouts.app>
