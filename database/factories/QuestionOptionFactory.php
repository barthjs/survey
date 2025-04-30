<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    public function definition(): array
    {
        return [
            'option_text' => $this->faker->words(5),
        ];
    }
}
