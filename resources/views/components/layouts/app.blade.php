@props(['title' => null])
<x-layouts.html :title="$title">
    <body class="app min-h-screen font-sans antialiased bg-base-200">

    <x-nav sticky full-width>
        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer"/>
            </label>

            <div>
                <a href="{{ route('dashboard') }}" wire:navigate.hover class="text-2xl">{{ config('app.name') }}</a>
            </div>
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <x-theme-toggle class="btn btn-circle btn-ghost"/>

            <x-dropdown>
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
                    <x-menu-item :title="__('Users')" icon="o-users" :link="route('users.index')"/>
                @endif
                <x-menu-separator/>

                <form method="POST" action="{{ route('logout') }}">
                    <x-button :label="__('Logout')" type="submit" icon="o-power"
                              class="btn btn-ghost w-full justify-start"/>
                    @csrf
                </form>
            </x-dropdown>
        </x-slot:actions>
    </x-nav>

    <x-main with-nav full-width>
        <x-slot:sidebar drawer="main-drawer" class="bg-base-200 lg:bg-inherit">
            <x-menu :activate-by-route="true">
                <x-menu-item :title="__('Dashboard')" icon="o-home" :link="route('dashboard')"/>
                <x-menu-item :title="__('Surveys')" icon="o-rectangle-stack" :link="route('surveys.index')"/>
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast/>

    </body>
</x-layouts.html>
