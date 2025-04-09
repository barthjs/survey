<div>
    <div class="min-h-screen flex items-center justify-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('profile') }}" class="font-medium underline px-3">{{ __('Profile')}}</a>
            <x-button label="{{ __('Log Out') }}" type="submit" icon="o-arrow-left-end-on-rectangle"
                      class="btn-primary"/>
        </form>
    </div>
</div>
