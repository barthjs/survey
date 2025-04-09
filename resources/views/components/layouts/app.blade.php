<x-layouts.html :title="$title">
    <body class="app min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <div class="ml-5 pt-5">{{ config('app.name') }}</div>
        </x-slot:brand>

        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer"/>
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <div class="ml-5 pt-5">
                <a href="{{ route('dashboard') }}" wire:navigate.hover>{{ config('app.name') }}</a>
            </div>

            {{-- MENU --}}
            <x-menu :activate-by-route="true">

                <x-menu-item :title="__('Dashboard')" icon="o-home" :link="route('dashboard')"/>
                <x-menu-item :title="__('Profil')" icon="o-user" :link="route('profile')"/>

                {{-- User --}}
                @if($user = auth()->user())
                    <x-menu-separator/>

                    <x-list-item :item="$user" value="name" sub-value="email">
                        <x-slot:actions>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-button type="submit" icon="o-power" class="btn-circle btn-ghost btn-xs"
                                          tooltip-left="{{ __('Logout') }}"/>
                            </form>
                        </x-slot:actions>
                    </x-list-item>
                @endif
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast/>
    </body>
</x-layouts.html>
