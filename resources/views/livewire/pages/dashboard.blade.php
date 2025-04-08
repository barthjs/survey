<div>
    <div class="min-h-screen flex items-center justify-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button label="{{ __('Log Out') }}" type="submit" icon="o-arrow-left-end-on-rectangle"
                      class="btn-primary"/>
        </form>
    </div>
</div>
