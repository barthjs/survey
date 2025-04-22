<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'question_text' => $this->faker->text(),
            'type' => $this->faker->randomElement(QuestionType::cases()),
            'is_required' => $this->faker->boolean(),
        ];
    }
}
