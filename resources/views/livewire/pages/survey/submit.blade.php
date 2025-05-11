@php use App\Enums\QuestionType; @endphp
<div class="w-full max-w-lg mx-auto">
    <x-header :title="$title" :subtitle="$description" separator></x-header>

    <x-form wire:submit="submitSurvey" novalidate autocomplete="off">
        @foreach ($questions as $question)
            <x-card :title="$question['question_text']">
                @if ($question['type'] === QuestionType::TEXT->name)
                    <x-input wire:model="response.{{ $question['id'] }}"/>

                @elseif ($question['type'] === QuestionType::MULTIPLE_CHOICE->name )
                    @foreach ($question['options'] as $option)
                        <x-checkbox
                            :id="$option['id']"
                            :label="$option['option_text']"
                            wire:model="response.{{ $question['id'] }}.{{ $option['id'] }}"
                        />
                    @endforeach

                @elseif ($question['type'] === QuestionType::FILE->name)
                    <x-file wire:model="response.{{ $question['id'] }}" required/>
                @endif
            </x-card>
        @endforeach

        <x-slot:actions class="justify-start">
            <x-button
                icon="o-check"
                :label="__('Submit survey')"
                spinner="submitSurvey"
                type="submit"
                class="btn-success"
            />
        </x-slot:actions>
    </x-form>
</div>
