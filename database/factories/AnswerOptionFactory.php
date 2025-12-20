<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnswerOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnswerOption>
 */
final class AnswerOptionFactory extends Factory
{
    protected $model = AnswerOption::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }
}
