<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionOption>
 */
final class QuestionOptionFactory extends Factory
{
    protected $model = QuestionOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'option_text' => $this->faker->text(),
        ];
    }
}
