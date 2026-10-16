<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<Survey>
 */
final class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->text(50),
            'description' => fake()->realText(),
            'is_public' => false,
            'is_active' => true,
            'end_date' => Date::now()->addMonths(3),
            'auto_closed_at' => Date::now()->addMonths(3),
        ];
    }

    public function public(): self
    {
        return $this->state(['is_public' => true]);
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }
}
