@php use App\Enums\QuestionType; @endphp
<div>
    <x-header :title="$response->survey->title" :subtitle="__('Response from').' '.$response->submitted_at" separator>
        <x-slot:actions>
            <x-button
                icon="o-arrow-left"
                :label="__('Back to survey')"
                :link="route('surveys.view', ['id' => $response->survey_id])"
                responsive
                class="btn-secondary"
            />
            <x-button
                icon="o-trash"
                :label="__('Delete response')"
                responsive
                wire:click="confirmDeletion"
                class="btn-error"
            />
            <x-confirm-delete :title="__('Delete response')" deleteAction="deleteResponse"/>
        </x-slot:actions>
    </x-header>

    <div class="space-y-3">
        @if(auth()->user()->is_admin)
            <x-card>
                <div class="text-sm space-y-1">
                    <div><strong>{{ __('IP Address') }}:</strong> {{ $response->ip_address ?? __('N/A') }}</div>
                    <div><strong>{{ __('User Agent') }}:</strong>
                        <code class="break-all">{{ $response->user_agent ?? __('N/A') }}</code>
                    </div>
                </div>
            </x-card>
        @endif

        @foreach($response->answers as $answer)
            <x-card wire:key="answer-{{ $answer->id }}">
                <h3 class="text-lg">{{ $answer->question->question_text }}</h3>

                @if($answer->question->type === QuestionType::TEXT)
                    <p class="mt-4">{{ $answer->answer_text }}</p>
                @elseif($answer->question->type === QuestionType::MULTIPLE_CHOICE)
                    <ul class="mt-4 list-disc list-inside">
                        @foreach($answer->selectedOptions as $option)
                            <li>{{ $option->option->option_text }}</li>
                        @endforeach
                    </ul>
                @elseif($answer->question->type === QuestionType::FILE)
                    @php
                        $filename = $answer->original_file_name;
                        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $icon = match($extension) {
                            'txt','doc','docx' => 'o-document-text',
                            'pdf' => 'o-document',
                            'jpg', 'jpeg', 'png' => 'o-photo',
                            default => 'o-question-mark-circle',
                        };
                    @endphp

                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <x-icon :name="$icon"/>
                            <span>{{ $filename }}</span>
                        </div>
                        <x-button
                            icon="o-arrow-down-tray"
                            wire:click="download('{{ $answer->id }}')"
                            class="btn-sm btn-ghost"
                        />
                    </div>
                @endif
            </x-card>
        @endforeach
    </div>
</div>
