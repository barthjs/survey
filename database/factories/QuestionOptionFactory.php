<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'option_text' => $this->faker->words(5),
        ];
    }
}
