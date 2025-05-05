<x-dropdown right>
    <x-slot:trigger>
        <x-icon name="o-globe-alt"/>
    </x-slot:trigger>

    @foreach(config('app.locales') as $locale => $language)
        <x-menu-item
            :icon="'c.language-' . $locale"
            :title="$language"
            :link="route('locale', ['locale' => $locale])"
            :active="app()->getLocale() === $locale"
        />
    @endforeach
</x-dropdown>
