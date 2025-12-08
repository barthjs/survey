<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'title' => $this->faker->text(50),
            'description' => $this->faker->realText(),
            'is_public' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(),
            'end_date' => Carbon::now()->addMonths(3),
            'auto_closed_at' => Carbon::now()->addMonths(3),
        ];
    }
}
