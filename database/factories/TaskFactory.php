<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $userId = User::first()->id ?? User::factory()->create()->id;

        return [
            'name' => fake()->sentence(),
            'description' => fake()->realText(),
            'due_date' => fake()->dateTimeBetween('now', '+1 year'),
            'status' => fake()->randomElement(['pending','in_progress','completed']),
            'priority' => fake()->randomElement(['low','medium','high']),
            'image_path' => fake()->imageUrl(),
            'assigned_user_id' => $userId,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }
}
