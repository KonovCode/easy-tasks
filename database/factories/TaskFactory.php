<?php

namespace Database\Factories;

use App\Enums\Task\PriorityEnum;
use App\Enums\Task\StatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'deadline' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'priority' => fake()->randomElement(PriorityEnum::cases()),
            'status' => fake()->randomElement(StatusEnum::cases()),
            'category_id' => null,
        ];
    }
}
