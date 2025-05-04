@props(['title' => null])
<x-layouts.html :title="$title">
    <body class="app min-h-screen font-sans antialiased bg-base-200">

    <x-nav sticky>
        <x-slot:brand>
            <div>
                <a href="{{ route('surveys.index') }}" wire:navigate.hover class="text-2xl">{{ config('app.name') }}</a>
            </div>
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <x-theme-toggle class="btn-circle btn-ghost"/>

            <x-dropdown :right="true">
                <x-slot:trigger>
                    <x-avatar :placeholder="auth()->user()->initials()" class="!w-8"/>
                </x-slot:trigger>

                <x-menu-item icon="o-user" :link="route('profile')">
                    {{ auth()->user()->name }}<br>
                    <div class="text-xs">
                        {{ auth()->user()->email }}
                    </div>
                </x-menu-item>
                @if(auth()->user()->is_admin)
                    <x-menu-item icon="o-users" :title="__('Users')" :link="route('users.index')"/>
                @endif
                <x-menu-separator/>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-button
                        icon="o-power"
                        :label="__('Logout')"
                        type="submit"
                        class="btn-ghost w-full justify-start"
                    />
                </form>
            </x-dropdown>
        </x-slot:actions>
    </x-nav>

    <x-main>
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast/>

    </body>
</x-layouts.html>
