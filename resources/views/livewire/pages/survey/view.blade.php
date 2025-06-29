@php use App\Enums\QuestionType; @endphp
<div class="w-full">
    <x-header :title="$survey->title" :subtitle="$survey->description" separator>
        <x-slot:actions>
            @auth
                <x-button
                    icon="o-x-circle"
                    :label="__('Cancel')"
                    :link="route('surveys.index')"
                    class="btn-secondary"
                />
                <x-button
                    icon="o-pencil"
                    :label="__('Edit')"
                    :link="route('surveys.edit', ['id' => $survey->id])"
                    class="btn-primary"
                />
                @if ($survey->is_active)
                    <x-dropdown :right="true">
                        <x-slot:trigger>
                            <x-button icon="o-share" :label="__('Share')" responsive class="btn-info"/>
                        </x-slot:trigger>

                        <div class="flex flex-col">
                            <x-button
                                icon="o-arrow-top-right-on-square"
                                :label="__('Submit')"
                                :link="route('surveys.submit', ['id' => $survey->id])"
                                external
                                class="btn-ghost justify-start"
                            />
                            <x-button
                                icon="o-clipboard"
                                :label="__('Copy link')"
                                x-on:click="navigator.clipboard.writeText('{{ route('surveys.submit', ['id' => $survey->id]) }}')"
                                class="btn-ghost justify-start"
                            />
                            <x-button
                                icon="o-paper-airplane"
                                :label="__('Send email')"
                                x-on:click="$wire.sendEmailModal = true"
                                class="btn-ghost  justify-start"
                            />
                        </div>
                    </x-dropdown>

                    <x-modal
                        :title="__('Send a link to this survey via email')"
                        wire:model="sendEmailModal"
                    >
                        <x-form wire:submit="sendEmail" novalidate autocomplete="off">
                            <x-input icon="o-envelope" :placeholder="__('Email address')" wire:model="email" required/>

                            <x-slot:actions>
                                <x-button
                                    icon="o-x-circle"
                                    :label="__('Cancel')"
                                    x-on:click="$wire.sendEmailModal = false"
                                    class="btn-secondary"
                                />
                                <x-button
                                    icon="o-paper-airplane"
                                    :label="__('Send')"
                                    spinner="sendEmail"
                                    type="submit"
                                    class="btn-info"
                                />
                            </x-slot:actions>
                        </x-form>
                    </x-modal>
                @endif
            @endauth
        </x-slot:actions>
    </x-header>

    <div class="space-y-3">
        <x-card :title="__('Details')">
            @if(auth()->check())
                <div class="flex items-center space-x-4">
                    <x-badge :value="$this->survey->is_public ? __('Public') : __('Private')" class="badge-primary"/>
                    <x-badge :value="__('Created at') . ': '.  $this->survey->created_at->format('Y-m-d H:i:s')"
                             class="badge-primary"/>
                    <x-badge
                        :value="$this->survey->is_active ? __('Open') : __('Closed')"
                        class="{{ $this->survey->is_active ? 'badge-success' : 'badge-error' }}"
                    />
                </div>
            @endif
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <x-stat
                    :title="__('Responses')"
                    :value="$survey->responses->count()"
                />
                <x-stat
                    :title="__('End date')"
                    :value="$survey->end_date?->format('Y-m-d H:i:s') ?? __('No end date')"
                />
            </div>
        </x-card>

        @foreach($questions as $questionIndex => $question)
            <x-card wire:key="question-{{ $questionIndex }}">
                <div class="flex items-center space-x-4">
                    <x-badge :value="__('Question') . ' ' . $questionIndex + 1" class="badge-primary"/>
                    <x-badge :value="$question['type']->label()" class="badge-info"/>
                    <x-badge :value="__('Answers') . ': '. count($question['answers'])" class="badge-secondary"/>
                </div>

                <div class="divider"></div>

                <h3 class="text-lg mb-4">{{ $question['question_text'] }}</h3>

                @if($question['type'] === QuestionType::TEXT)
                    <div class="space-y-4">
                        @foreach($question['answers'] as $answerIndex => $answer)
                            <x-collapse separator>
                                <x-slot:heading>
                                    {{ $answer['response']['submitted_at']->format('Y-m-d H:i:s') }}
                                </x-slot:heading>
                                <x-slot:content>
                                    <a
                                        href="{{ route('surveys.response', ['id' => $answer['response']['id']]) }}"
                                        wire:navigate.hover
                                    >
                                        {{ $answer['answer_text'] }}
                                    </a>
                                </x-slot:content>
                            </x-collapse>
                        @endforeach
                    </div>
                @elseif($question['type'] === QuestionType::MULTIPLE_CHOICE)
                    @if(count($question['answers']) > 0)
                        <div class="canvas-container" style="height: 20rem">
                            <canvas id="chart-{{ $question['id'] }}"></canvas>
                            <script>
                                document.addEventListener('livewire:initialized', () => {
                                    const ctx = document.getElementById('chart-{{ $question['id'] }}').getContext('2d');
                                    const chartData = @json($this->getChartData($question['id']));
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
                @elseif($question['type'] === QuestionType::FILE)
                    <ul class="space-y-4">
                        @foreach($question['answers'] as $answerIndex => $answer)
                            <li class="flex items-center justify-between p-2 border rounded">
                                <a
                                    href="{{ route('surveys.response', ['id' => $answer['response']['id']]) }}"
                                    wire:navigate.hover
                                >
                                    <div class="flex items-center space-x-4">
                                        <span>{{ $answer['response']['submitted_at']->format('Y-m-d H:i:s')  }}</span>
                                        <x-icon
                                            :name="QuestionType::getIconFromFilename($answer['original_file_name'])"/>
                                        <span>{{ $answer['original_file_name'] }}</span>
                                    </div>
                                </a>

                                <x-button
                                    icon="o-arrow-down-tray"
                                    wire:click="download('{{ $answer['id'] }}')"
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
