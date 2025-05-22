@php use App\Enums\QuestionType; @endphp
<div>
    <x-header :title="__('Edit survey')" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-trash"
                :label="__('Delete survey')"
                responsive
                wire:click="confirmDeletion"
                class="btn-error"
            />
            <x-button
                icon="o-check"
                :label="__('Update survey')"
                responsive
                spinner="updateSurvey"
                wire:click="updateSurvey"
                class="btn-success"
            />
            <x-confirm-delete :title="__('Delete survey')" deleteAction="deleteSurvey"/>
        </x-slot:actions>
    </x-header>

    <x-form wire:submit="updateSurvey" novalidate autocomplete="off">
        <x-card>
            <x-input :label="__('Title')" wire:model="title" required/>
            <x-textarea :label="__('Description')" :hint="__('Max 1000 chars')" wire:model="description" rows="5"/>
            <x-datetime :label="__('End date')" wire:model="closed_at" type="datetime-local"/>
            <div class="mt-4">
                <x-checkbox :label="__('Public')" wire:model="is_public"/>
                <x-checkbox :label="__('Active')" wire:model="is_active"/>
            </div>
        </x-card>

        @if ($errors->has('questions'))
            <div class="text-error">
                {{ $errors->first('questions') }}
            </div>
        @endif

        @foreach($questions as $questionIndex => $question)
            <x-card wire:key="question-{{ $questionIndex }}">
                <div class="flex items-center space-x-4">
                    <x-badge :value="__('Question') . ' ' . $questionIndex + 1" class="badge-primary"/>
                    <x-checkbox
                        :label="__('Required')"
                        wire:model="questions.{{ $questionIndex }}.is_required"
                        x-on:click="{{ count($this->questions) === 1 ? '$event.preventDefault()' : '' }}"
                    />
                </div>

                <div class="divider"></div>

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
                                        icon="o-x-mark"
                                        spinner="removeOption({{ $questionIndex }}, {{ $optionIndex }})"
                                        wire:click="removeOption({{ $questionIndex }}, {{ $optionIndex }})"
                                        class="btn-ghost text-error"
                                    />
                                @endif
                            </div>
                        @endforeach

                        <x-button
                            icon="o-plus"
                            :label="__('Add option')"
                            spinner="addOption({{ $questionIndex }})"
                            wire:click="addOption({{ $questionIndex }})"
                            class="btn-outline"
                        />
                    </div>
                @endif

                <x-slot:actions>
                    @if(count($questions) > 1)
                        <x-button
                            icon="o-trash"
                            :label="__('Delete')"
                            responsive
                            spinner="removeQuestion({{ $questionIndex }})"
                            wire:click="removeQuestion({{ $questionIndex }})"
                            class="btn-error"
                        />
                    @endif
                </x-slot:actions>
            </x-card>
        @endforeach

        <x-slot:actions class="justify-start">
            <x-button
                icon="o-plus"
                :label="__('Add question')"
                spinner="addQuestion"
                wire:click="addQuestion"
                class="btn-warning"
            />
        </x-slot:actions>
    </x-form>
</div>
