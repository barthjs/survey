@php use App\Enums\QuestionType; @endphp
<div class="w-full max-w-lg mx-auto">
    <x-header :title="$title" :subtitle="$description" separator/>

    <x-form wire:submit="submitSurvey" novalidate autocomplete="off">
        @foreach ($questions as $question)
            <x-card :title="$question['question_text']">
                @if ($question['type'] === QuestionType::TEXT->name)
                    <x-input
                        :label="$question['question_text']"
                        wire:model="response.{{ $question['id'] }}"
                        :required="$question['is_required']"
                    />
                @elseif ($question['type'] === QuestionType::MULTIPLE_CHOICE->name )
                    @if ($question['is_required'])
                        <span class="text-error">*</span>
                    @endif
                    @foreach ($question['options'] as $option)
                        <x-checkbox
                            :id="$option['id']"
                            :label="$option['option_text']"
                            wire:model="response.{{ $question['id'] }}.{{ $option['id'] }}"
                        />
                    @endforeach
                @elseif ($question['type'] === QuestionType::FILE->name)
                    <x-file wire:model="response.{{ $question['id'] }}" :required="$question['is_required']"/>
                @endif
            </x-card>
        @endforeach

        @if($survey->closed_at)
            <h2 class="text-lg text-info"> {{ __('Open until') . ': ' .   $survey->closed_at }}</h2>
        @endif

        <x-slot:actions class="justify-start">
            <x-button
                :label="__('Submit')"
                spinner="submitSurvey"
                type="submit"
                class="btn-success"
            />
        </x-slot:actions>
    </x-form>
</div>
