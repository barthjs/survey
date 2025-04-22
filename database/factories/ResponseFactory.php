<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Response;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'submitted_at' => Carbon::now(),
        ];
    }
}
