<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'title' => $this->faker->text(50),
            'description' => $this->faker->realText(),
            'is_active' => $this->faker->boolean(),
            'closed_at' => Carbon::now()->addMonths(3),
        ];
    }
}
