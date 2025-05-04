@php use App\Enums\QuestionType; @endphp
<div x-data="surveyBuilder()" x-init="init()">
    <x-header :title="__('Create survey')" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.index')"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-check"
                :label="__('Create survey')"
                responsive
                spinner="createSurvey"
                x-on:click="submitSurvey"
                class="btn-success"
            />
        </x-slot:actions>
    </x-header>

    <x-form x-on:submit.prevent="submitSurvey" novalidate autocomplete="off">
        <x-card>
            <x-input :label="__('Title')" wire:model="title" required/>
            <x-textarea :label="__('Description')" :hint="__('Max 1000 chars')" wire:model="description" rows="5"/>
            <x-datetime :label="__('End date')" wire:model="closed_at" type="datetime-local"/>
        </x-card>

        <template x-for="(question, questionIndex) in questions">
            <x-card>
                <div class="flex items-center">
                    <x-badge x-text="'{{ __('Question') }} ' + (questionIndex + 1)" class="badge-primary mr-4"/>
                    <x-checkbox :label="__('Required')" x-model="question.is_required"/>
                </div>

                <x-input :label="__('Question Text')" x-model="question.question_text" required/>
                <div
                    class="text-error"
                    x-text="getError(`questions.${questionIndex}.question_text`)">
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
                                        <x-input x-model="question.options[optionIndex]" class="w-full"/>
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
                                    x-text="getError(`questions.${questionIndex}.options.${optionIndex}`)">
                                </div>
                            </div>
                        </template>

                        <x-button
                            icon="o-plus"
                            :label="__('Add option')"
                            x-on:click="addOption(questionIndex)"
                            class="btn-outline"
                        />
                    </div>
                </template>

                <x-slot:actions>
                    <x-button
                        icon="o-trash"
                        :label="__('Delete')"
                        :responsive="true"
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
                x-on:click="addQuestion()"
                class="btn-warning"
            />
        </x-slot:actions>
    </x-form>

    <script>
        function surveyBuilder() {
            return {
                questions: [],
                errors: {},
                questionTemplate() {
                    return {
                        question_text: '',
                        type: 'TEXT',
                        is_required: false,
                        options: [],
                    };
                },
                init() {
                    this.questions = [this.questionTemplate()];

                    Livewire.on('validationErrors', (errors) => {
                        this.errors = Array.isArray(errors) ? errors[0] : errors;
                    });
                },
                addQuestion() {
                    this.questions.push(this.questionTemplate());
                },
                removeQuestion(questionIndex) {
                    if (this.questions.length <= 1) return;
                    this.questions.splice(questionIndex, 1);
                },
                addOption(questionIndex) {
                    this.questions[questionIndex].options.push('');
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
                        question.options = ['', ''];
                    }
                },
                submitSurvey() {
                    this.errors = {};

                    const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    component.set('questions', this.questions);
                    component.call('createSurvey');
                },
                getError(path) {
                    return this.errors[path]?.[0] ?? '';
                }
            }
        }
    </script>
</div>
