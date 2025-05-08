@php use App\Enums\QuestionType; @endphp
<div>
    <x-header :title="$survey->title" :subtitle="$survey->description" separator class="w-full max-w-lg mx-auto">
        <x-slot:actions>
            <x-button
                icon="o-check"
                :label="__('Submit survey')"
                spinner="submitSurvey"
                wire:click="submitSurvey"
                class="btn-success"
            />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="submitSurvey" novalidate autocomplete="off">
        @foreach ($questions as $questionIndex => $question)
            <x-card :title="$question['question_text']" wire:key="{{ $question->id  }}">
                @if ($question->type === QuestionType::TEXT)
                    <x-input wire:model="response.{{ $question->id }}.answer_text" required/>
                @elseif ($question->type === QuestionType::MULTIPLE_CHOICE )
                    @foreach ($question['options'] as $optionIndex => $option)
                        <div class="flex-1">
                            <x-checkbox
                                :id="$option->id"
                                :label="$option->option_text"
                                wire:model="response.{{ $question->id }}.{{ $option->id }}"
                                required
                            />
                        </div>
                    @endforeach
                @elseif($question->type === QuestionType::FILE)
                    <x-file wire:model="response.{{ $question->id }}.file_path" required/>
                @endif
            </x-card>
        @endforeach
    </x-form>
</div>
