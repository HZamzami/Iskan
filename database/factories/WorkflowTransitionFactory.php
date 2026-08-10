<?php

namespace Database\Factories;

use App\Enums\WorkflowAction;
use App\Enums\WorkflowStatus;
use App\Models\User;
use App\Models\WorkflowTransition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowTransition>
 */
class WorkflowTransitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_status' => null,
            'to_status' => WorkflowStatus::Pending,
            'action' => WorkflowAction::Submit,
            'actor_id' => User::factory(),
            'assigned_to_id' => User::factory(),
        ];
    }
}
