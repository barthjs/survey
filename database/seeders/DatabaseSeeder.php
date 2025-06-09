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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Random\RandomException;
use Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @throws RandomException
     */
    public function run(): void
    {
        $admin = null;
        if (User::where('is_admin', '=', true)->count() === 0) {
            $admin = User::create(
                [
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('admin'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_admin' => true,
                ]
            );
        }

        if (App::isLocal()) {
            $user = User::firstOrCreate(['email' => 'user@example.com'],
                [
                    'name' => 'Demo User',
                    'password' => Hash::make('user'),
                    'email_verified_at' => now(),
                ]
            );

            $admin && $this->createDemoSurveys($admin);

            $this->createDemoSurveys($user);

            User::factory(10)->create(['password' => 'password']);
        }
    }

    /**
     * @throws RandomException
     */
    private function createDemoSurveys(User $user): void
    {
        $surveys = new Collection;
        for ($i = 1; $i <= 10; $i++) {
            $surveys->add(Survey::factory()->create([
                'user_id' => $user->id,
                'title' => "Demo $i",
                'description' => 'This is a demo survey.',
                'auto_closed_at' => null,
            ]));
        }

        foreach ($surveys as $survey) {
            $questions = $this->createQuestions($survey);

            for ($i = 1; $i <= 10; $i++) {
                $response = Response::factory()->create([
                    'survey_id' => $survey->id,
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

                        case QuestionType::MULTIPLE_CHOICE:
                            $options = $question->options()->inRandomOrder()->take(random_int(1, 4))->get();

                            foreach ($options as $option) {
                                AnswerOption::factory()->create([
                                    'answer_id' => $answer->id,
                                    'question_option_id' => $option->id,
                                ]);
                            }
                            break;

                        case QuestionType::FILE:
                            $originalName = 'dummy-file'.fake()->word().'.txt';
                            $path = "surveys/$survey->id/$originalName";
                            Storage::disk('local')->put($path, fake()->realText());
                            $answer->update([
                                'file_path' => $path,
                                'original_file_name' => $originalName,
                            ]);
                            break;
                    }
                }
            }
        }
    }

    private function createQuestions(Survey $survey): array
    {
        $questions = [];

        $questions[] = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Question 1',
            'type' => QuestionType::TEXT,
            'is_required' => true,
            'order_index' => 0,
        ]);

        $questions[] = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Question 2',
            'type' => QuestionType::MULTIPLE_CHOICE,
            'is_required' => false,
            'order_index' => 1,
        ]);

        for ($j = 0; $j < 3; $j++) {
            QuestionOption::factory()->create([
                'question_id' => $questions[1]->id,
                'option_text' => "Option $j",
                'order_index' => $j,
            ]);
        }

        $questions[] = Question::create([
            'survey_id' => $survey->id,
            'question_text' => 'Question 3',
            'type' => QuestionType::FILE,
            'is_required' => false,
            'order_index' => 2,
        ]);

        return $questions;
    }
}
