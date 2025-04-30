@php use App\Enums\QuestionType; @endphp
<div>
    <x-header :title="__('Edit survey')" :separator="true">
        <x-slot:actions>
            <x-button
                :label="__('Delete survey')"
                wire:click="deleteSurvey"
                icon="o-trash"
                class="btn-sm btn-error"
            />
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="updateSurvey" novalidate autocomplete="off">
        <x-card>
            <x-input :label="__('Title')" wire:model="title" required/>
            <x-textarea :label="__('Description')" :hint="__('Max 1000 chars')" wire:model="description" rows="5"/>
            <x-datetime :label="__('End date')" wire:model="closed_at" type="datetime-local"/>
        </x-card>

        @foreach($questions as $questionIndex => $question)
            <x-card wire:key="question-{{ $questionIndex }}">
                <div class="flex items-center">
                    <x-badge :value="__('Question') . ' ' . $questionIndex + 1" class="badge-primary mr-4"></x-badge>
                    <x-checkbox :label="__('Required')" wire:model="questions.{{ $questionIndex }}.is_required"/>
                </div>

                <x-input
                    :label="__('Question Text')"
                    wire:model="questions.{{ $questionIndex }}.question_text"
                    required
                />

                <x-select
                    :label="__('Type')"
                    :options="QuestionType::toArray()"
                    wire:model="questions.{{ $questionIndex }}.type"
                    wire:change="handleQuestionTypeChange({{ $questionIndex }}, $event.target.value)"
                    required
                    class="mb-4"
                />

                @if($question['type'] === QuestionType::MULTIPLE_CHOICE)
                    <div class="space-y-4">
                        @foreach($question['options'] as $optionIndex => $option)
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <x-input
                                        wire:model="questions.{{ $questionIndex }}.options.{{ $optionIndex }}.option_text"
                                        class="w-full"
                                    />
                                </div>
                                @if(count($question['options']) > 2)
                                    <x-button
                                        spinner="removeOption({{ $questionIndex }}, {{ $optionIndex }})"
                                        wire:click="removeOption({{ $questionIndex }}, {{ $optionIndex }})"
                                        icon="o-x-mark"
                                        class="btn-ghost text-error"
                                    />
                                @endif
                            </div>
                        @endforeach

                        <x-button
                            :label="__('Add option')"
                            spinner="addOption({{ $questionIndex  }})"
                            wire:click="addOption({{ $questionIndex  }})"
                            icon="o-plus"
                            class="btn-outline"
                        />
                    </div>
                @endif

                <x-slot:actions>
                    @if(count($questions) > 1)
                        <x-button
                            :label="__('Delete')"
                            :responsive="true"
                            spinner="removeQuestion({{ $questionIndex }})"
                            wire:click="removeQuestion({{ $questionIndex }})"
                            icon="o-trash"
                            class="btn-error"
                        />
                    @endif
                </x-slot:actions>
            </x-card>
        @endforeach

        <x-slot:actions class="justify-start">
            <x-button
                :label="__('Add question')"
                spinner="addQuestion"
                wire:click="addQuestion"
                icon="o-plus"
                class="btn-warning"
            />
            <x-button
                :label="__('Update survey')"
                spinner="updateSurvey"
                type="submit"
                class="btn-success"
            />
        </x-slot:actions>
    </x-form>
</div>
