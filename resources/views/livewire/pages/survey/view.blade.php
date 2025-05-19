@php use App\Enums\QuestionType; @endphp
<div>
    <x-header :title="$survey->title" :subtitle="$survey->description" separator>
        <x-slot:actions>
            <x-button
                icon="o-x-circle"
                :label="__('Cancel')"
                :link="route('surveys.index')"
                class="btn-secondary"
            />
            <x-button
                icon="o-pencil"
                :label="__('Edit survey')"
                :link="route('surveys.edit', ['id' => $survey->id])"
                class="btn-primary"
            />
            @if (
                (! $this->survey->closed_at || ! $this->survey->closed_at->isPast()) &&
                $this->survey->is_active
            )
                <x-button
                    icon="o-arrow-top-right-on-square"
                    :label="__('Submit')"
                    :link="route('surveys.submit', ['id' => $survey->id])"
                    external
                    class="btn-info"
                />
            @endif
        </x-slot:actions>
    </x-header>

    <div class="space-y-3">
        <x-card>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <x-stat
                    :title="__('Responses')"
                    :value="$survey->responses->count()"
                />
                <x-stat
                    :title="__('Closed at')"
                    :value="$survey->closed_at"
                />
            </div>
        </x-card>

        @foreach($questions as $questionIndex => $question)
            <x-card wire:key="question-{{ $questionIndex }}">
                <div class="flex items-center space-x-4">
                    <x-badge :value="__('Question') . ' ' . $questionIndex + 1" class="badge-primary"/>
                    <x-badge :value="$question->type->label()" class="badge-info"/>
                    <x-badge :value="__('Answers') . ': '. $question->answers->count()" class="badge-secondary"/>
                </div>

                <div class="divider"></div>

                <h3 class="text-lg mb-4">{{ $question->question_text }}</h3>

                @if($question->type === QuestionType::TEXT)
                    <div class="space-y-4">
                        @foreach($question->answers as $answer)
                            <x-collapse separator>
                                <x-slot:heading>
                                    {{ $answer->response->submitted_at }}
                                </x-slot:heading>
                                <x-slot:content>
                                    <a
                                        href="{{ route('surveys.response', ['id' => $answer->response->id]) }}"
                                        wire:navigate.hover
                                    >
                                        {{ $answer->answer_text }}
                                    </a>
                                </x-slot:content>
                            </x-collapse>
                        @endforeach
                    </div>
                @elseif($question->type === QuestionType::MULTIPLE_CHOICE)
                    @if($question->answers->count() > 0)
                        <div class="canvas-container" style="height: 20rem">
                            <canvas id="chart-{{ $question->id }}"></canvas>
                            <script>
                                document.addEventListener('livewire:initialized', () => {
                                    const ctx = document.getElementById('chart-{{ $question->id }}').getContext('2d');
                                    const chartData = @json($this->getChartData($question));
                                    if (!chartData) {
                                        return;
                                    }

                                    new Chart(ctx, {
                                        type: 'pie',
                                        data: chartData,
                                        options: {
                                            plugins: {
                                                legend: {
                                                    position: 'bottom'
                                                }
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>
                    @endif
                @elseif($question->type === QuestionType::FILE)
                    <ul class="space-y-4">
                        @foreach($question->answers as $answer)
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

                            <li class="flex items-center justify-between p-2 border rounded">
                                <a
                                    href="{{ route('surveys.response', ['id' => $answer->response->id]) }}"
                                    wire:navigate.hover
                                >
                                    <div class="flex items-center space-x-4">
                                        <span>{{ $answer->response->submitted_at }}</span>
                                        <x-icon :name="$icon"/>
                                        <span>{{ $filename }}</span>
                                    </div>
                                </a>

                                <x-button
                                    icon="o-arrow-down-tray"
                                    wire:click="download('{{ $answer->id }}')"
                                    class="btn-sm btn-ghost"
                                >
                                </x-button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        @endforeach
    </div>
</div>
