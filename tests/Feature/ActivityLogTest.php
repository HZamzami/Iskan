<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Models\Minute;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_operations_are_logged_with_causer(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $minute = Minute::factory()->create();

        $created = Activity::query()
            ->where('subject_type', Minute::class)
            ->where('subject_id', $minute->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($created);

        $minute->update(['title' => 'محضر محدث']);

        $updated = Activity::query()
            ->where('subject_type', Minute::class)
            ->where('subject_id', $minute->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($updated);
        $this->assertSame('محضر محدث', $updated->attribute_changes['attributes']['title']);
        $this->assertArrayHasKey('title', $updated->attribute_changes['old']);

        $minute->delete();

        $this->assertNotNull(Activity::query()
            ->where('subject_type', Minute::class)
            ->where('event', 'deleted')
            ->first());
    }

    public function test_authenticated_user_is_recorded_as_causer(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $minute = Minute::factory()->create();

        $activity = Activity::query()
            ->where('subject_type', Minute::class)
            ->where('subject_id', $minute->id)
            ->firstOrFail();

        $this->assertTrue($activity->causer->is($admin));
    }

    public function test_password_is_never_logged_for_users(): void
    {
        $this->actingAs($this->makeAdminUser());

        $user = User::factory()->create();
        $user->update(['password' => 'new-secret-password', 'name' => 'اسم جديد']);

        Activity::query()
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->get()
            ->each(function (Activity $activity): void {
                $this->assertArrayNotHasKey('password', $activity->attribute_changes['attributes'] ?? []);
                $this->assertArrayNotHasKey('password', $activity->attribute_changes['old'] ?? []);
            });
    }

    public function test_non_admin_cannot_access_activity_log(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        Livewire::test(ListActivities::class)
            ->assertForbidden();
    }

    public function test_admin_can_view_activity_log(): void
    {
        $this->actingAs($this->makeAdminUser());

        $minute = Minute::factory()->create();

        Livewire::test(ListActivities::class)
            ->assertCanSeeTableRecords(
                Activity::query()->where('subject_type', Minute::class)->get(),
            );
    }

    public function test_subject_label_shows_reference_number_for_archive_records(): void
    {
        $minute = Minute::factory()->create();

        $this->assertSame(
            $minute->reference_number,
            ActivityResource::subjectLabel(Minute::class, $minute->id),
        );
    }

    public function test_subject_label_shows_name_and_title_for_users_and_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $this->assertSame($user->name, ActivityResource::subjectLabel(User::class, $user->id));
        $this->assertSame($task->title, ActivityResource::subjectLabel(Task::class, $task->id));
    }

    public function test_subject_label_falls_back_to_raw_id_when_record_is_gone(): void
    {
        $minute = Minute::factory()->create();
        $minuteId = $minute->id;
        $minute->delete();

        $this->assertSame("#{$minuteId}", ActivityResource::subjectLabel(Minute::class, $minuteId));
    }
}
