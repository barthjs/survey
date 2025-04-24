<?php

declare(strict_types=1);

namespace Database\Seeders;

use App;
use App\Enums\QuestionType;
use App\Models\Answer;
use App\Models\AnswerOption;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Response;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(['name' => 'Admin User'],
            [
                'email' => 'admin@example.com',
                'password' => Hash::make('admin'),
                'email_verified_at' => now(),
            ]
        );
        if (App::isLocal()) {
            $user = User::firstOrCreate(['name' => 'User User'],
                [
                    'email' => 'user@example.com',
                    'password' => Hash::make('user'),
                    'email_verified_at' => now(),
                ]
            );
            $this->createDemoSurveys($admin);
            $this->createDemoSurveys($user);
        }
    }

    private function createDemoSurveys(User $user): void
    {
        $surveys = Survey::factory(20)->create(['user_id' => $user->id]);

        foreach ($surveys as $survey) {
            $questions = [];

            for ($i = 1; $i <= 10; $i++) {

                $question = Question::factory()->create([
                    'survey_id' => $survey->id,
                    'question_text' => "Question $i",
                    'is_required' => $i % 2 === 0,
                    'order_index' => $i,
                ]);

                if ($question->type === QuestionType::MULTIPLE_CHOICE) {
                    for ($j = 1; $j <= 4; $j++) {
                        QuestionOption::factory()->create([
                            'question_id' => $question->id,
                            'option_text' => "Option $j",
                            'order_index' => $j,
                        ]);
                    }
                }

                $questions[] = $question;
            }

            for ($i = 1; $i <= 10; $i++) {
                $response = Response::factory()->create([
                    'survey_id' => $survey->id,
                    'submitted_at' => now(),
                ]);

                foreach ($questions as $question) {
                    $answer = Answer::factory()->create([
                        'response_id' => $response->id,
                        'question_id' => $question->id,
                    ]);

                    switch ($question->type) {
                        case QuestionType::TEXT:
                            $answer->update([
                                'answer_text' => fake()->sentence(),
                            ]);
                            break;

                        case QuestionType::FILE:
                            $answer->update([
                                'file_path' => 'uploads/dummy-file-'.fake()->uuid().'.pdf',
                            ]);
                            break;

                        case QuestionType::MULTIPLE_CHOICE:
                            $options = $question->options()->inRandomOrder()->take(rand(1, 2))->get();

                            foreach ($options as $option) {
                                AnswerOption::factory()->create([
                                    'answer_id' => $answer->id,
                                    'option_id' => $option->id,
                                ]);
                            }
                            break;
                    }
                }
            }
        }
    }
}
