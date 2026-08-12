<?php

namespace Tests\Feature;

use App\Filament\Widgets\MyAssignedRecordsWidget;
use App\Models\GeoDocument;
use App\Models\Minute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MyAssignedRecordsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_records_assigned_to_the_current_user_across_modules(): void
    {
        $user = $this->makeAdminUser();
        $this->actingAs($user);

        GeoDocument::factory()->create(['assigned_to' => $user->id, 'title' => 'خريطة بانتظاري']);
        Minute::factory()->create(['assigned_to' => User::factory()->create()->id, 'title' => 'محضر لغيري']);
        GeoDocument::factory()->create(['assigned_to' => null, 'title' => 'خريطة غير مسندة']);

        Livewire::test(MyAssignedRecordsWidget::class)
            ->assertSee('خريطة بانتظاري')
            ->assertDontSee('محضر لغيري')
            ->assertDontSee('خريطة غير مسندة');
    }
}
