@php use App\Enums\QuestionType; @endphp
@props(['questions' => []])
<div x-data="surveyBuilder()" x-init="init()" x-on:save-survey.window="submitSurvey()">
    <x-form novalidate autocomplete="off">
        <x-card>
            <x-input :label="__('Title')" wire:model="title" required/>
            <x-textarea :label="__('Description')" :hint="__('Max 1000 chars')" wire:model="description" rows="5"/>
            <div class="flex">
                <x-datetime :label="__('End date')" wire:model="end_date" type="datetime-local"/>
            </div>
            <div class="mt-4">
                <x-checkbox :label="__('Public')" wire:model="is_public"/>
                <x-checkbox :label="__('Active')" wire:model="is_active"/>
            </div>
        </x-card>

        <div x-show="!questions.some(q => q.is_required)" class="text-error">
            {{ __('At least one question must be marked as required.') }}
        </div>

        <template x-for="(question, questionIndex) in questions">
            <x-card>
                <div class="flex items-center">
                    <x-badge x-text="'{{ __('Question') }} ' + (questionIndex + 1)" class="badge-primary mr-4"/>
                    <x-checkbox
                        :label="__('Required')"
                        x-model="question.is_required"
                        x-on:click="if (questions.length === 1) $event.preventDefault()"
                    />
                </div>

                <x-input :label="__('Question text')" x-model="question.question_text" required/>
                <div
                    class="text-sm text-error"
                    x-text="getError(`questions.${questionIndex}.question_text`)"
                >
                </div>

                <x-select
                    :label="__('Type')"
                    :options="QuestionType::toArray()"
                    x-model="question.type"
                    x-on:change="handleQuestionTypeChange(questionIndex)"
                    required
                    class="mb-4"
                />

                <template x-if="question.type === '{{ QuestionType::MULTIPLE_CHOICE->value }}'">
                    <div class="space-y-4">
                        <template x-for="(option, optionIndex) in question.options">
                            <div class="space-y-1">
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <x-input x-model="question.options[optionIndex].option_text" class="w-full"/>
                                    </div>
                                    <x-button
                                        icon="o-x-mark"
                                        x-show="question.options.length > 2"
                                        x-on:click="removeOption(questionIndex, optionIndex)"
                                        class="btn-ghost text-error"
                                    />
                                </div>
                                <div
                                    class="text-error"
                                    x-text="getError(`questions.${questionIndex}.options.${optionIndex}.option_text`)">
                                </div>
                            </div>
                        </template>

                        <x-button
                            icon="o-plus"
                            :label="__('Add option')"
                            x-show="question.options.length < 10"
                            x-on:click="addOption(questionIndex)"
                            class="btn-outline"
                        />
                    </div>
                </template>

                <x-slot:actions>
                    <x-button
                        icon="o-trash"
                        :label="__('Delete')"
                        responsive
                        x-show="questions.length > 1"
                        x-on:click="removeQuestion(questionIndex)"
                        class="btn-error"
                    />
                </x-slot:actions>
            </x-card>
        </template>

        <x-slot:actions class="justify-start">
            <x-button
                icon="o-plus"
                :label="__('Add question')"
                x-show="questions.length < 100"
                x-on:click="addQuestion()"
                class="btn-warning"
            />
        </x-slot:actions>
    </x-form>

    <script>
        function surveyBuilder() {
            return {
                questions: {{ Js::from($questions) }},
                errors: {},

                questionTemplate() {
                    return {
                        question_text: '',
                        type: 'TEXT',
                        is_required: true,
                        options: [],
                    };
                },

                init() {
                    Livewire.on('validationErrors', (errors) => {
                        this.errors = Array.isArray(errors) ? errors[0] : errors;
                    });
                },

                addQuestion() {
                    if (this.questions.length >= 100) {
                        return;
                    }

                    this.questions.push(this.questionTemplate());
                },

                removeQuestion(questionIndex) {
                    if (this.questions.length <= 1) return;
                    this.questions.splice(questionIndex, 1);

                    if (this.questions.length === 1) {
                        this.questions[0]['is_required'] = true;
                    }
                },

                addOption(questionIndex) {
                    const options = this.questions[questionIndex].options;
                    if (options.length >= 10) {
                        return;
                    }

                    this.questions[questionIndex].options.push({option_text: ''});
                },
                removeOption(questionIndex, optionIndex) {
                    if (this.questions[questionIndex].options.length <= 2) return;
                    this.questions[questionIndex].options.splice(optionIndex, 1);
                },

                handleQuestionTypeChange(questionIndex) {
                    const question = this.questions[questionIndex];

                    if (question.type !== 'MULTIPLE_CHOICE') {
                        question.options = [];
                    } else if (!question.options.length) {
                        this.questions[questionIndex].options.push(
                            ...[
                                {option_text: ''},
                                {option_text: ''}
                            ]
                        );

                    }
                },

                submitSurvey() {
                    this.errors = {};

                    if (!this.questions.some(q => q.is_required)) {
                        return;
                    }

                    const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    component.set('questions', this.questions);
                    component.call('save');
                },

                getError(path) {
                    return this.errors[path]?.[0] ?? '';
                }
            }
        }
    </script>
</div>
