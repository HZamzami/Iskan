<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
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
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'assigned_to' => User::factory(),
            'assigned_role_id' => Role::factory(),
            'requested_by' => User::factory(),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'status' => TaskStatus::Pending,
            'recurrence' => TaskRecurrence::Once,
            'is_template' => false,
            'is_active' => true,
            'notify_by_email' => false,
        ];
    }

    public function recurringTemplate(TaskRecurrence $recurrence = TaskRecurrence::Weekly): static
    {
        return $this->state(fn (): array => [
            'recurrence' => $recurrence,
            'is_template' => true,
            'next_run_date' => $this->faker->dateTimeBetween('now', '+1 week'),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
