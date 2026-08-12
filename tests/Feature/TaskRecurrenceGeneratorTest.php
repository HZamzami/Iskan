<?php

namespace Tests\Feature;

use App\Enums\TaskRecurrence;
use App\Models\Task;
use App\Services\TaskRecurrenceGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskRecurrenceGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_an_instance_for_a_due_template(): void
    {
        Notification::fake();

        $template = Task::factory()->recurringTemplate(TaskRecurrence::Weekly)->create([
            'next_run_date' => today(),
        ]);

        $generated = app(TaskRecurrenceGenerator::class)->run();

        $this->assertSame(1, $generated);
        $this->assertSame(1, Task::query()->instances()->where('parent_task_id', $template->id)->count());

        $template->refresh();
        $this->assertTrue($template->next_run_date->isSameDay(today()->addWeek()));
    }

    public function test_rerunning_on_the_same_day_is_idempotent(): void
    {
        Notification::fake();

        Task::factory()->recurringTemplate(TaskRecurrence::Daily)->create([
            'next_run_date' => today(),
        ]);

        $generator = app(TaskRecurrenceGenerator::class);
        $generator->run();
        $secondRunCount = $generator->run();

        $this->assertSame(0, $secondRunCount);
        $this->assertSame(1, Task::query()->instances()->count());
    }

    public function test_inactive_templates_are_skipped(): void
    {
        Notification::fake();

        Task::factory()->recurringTemplate(TaskRecurrence::Daily)->create([
            'next_run_date' => today(),
            'is_active' => false,
        ]);

        $generated = app(TaskRecurrenceGenerator::class)->run();

        $this->assertSame(0, $generated);
        $this->assertSame(0, Task::query()->instances()->count());
    }
}
