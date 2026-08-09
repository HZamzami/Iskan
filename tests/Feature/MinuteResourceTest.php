<?php

namespace Tests\Feature;

use App\Filament\Resources\Minutes\Pages\CreateMinute;
use App\Filament\Resources\Minutes\Pages\ListMinutes;
use App\Models\Minute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MinuteResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_list_page_shows_minutes(): void
    {
        $minutes = Minute::factory()->count(3)->create();

        Livewire::test(ListMinutes::class)
            ->assertCanSeeTableRecords($minutes);
    }

    public function test_type_tab_filters_records(): void
    {
        $weeklyMeeting = Minute::factory()
            ->ofType('weekly_meeting', 'site_a')
            ->create();
        $projectHandover = Minute::factory()
            ->ofType('project_handover')
            ->create();

        Livewire::test(ListMinutes::class)
            ->set('activeTab', 'weekly_meeting')
            ->assertCanSeeTableRecords([$weeklyMeeting])
            ->assertCanNotSeeTableRecords([$projectHandover]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = Minute::factory()
            ->ofType('weekly_meeting', 'site_a')
            ->create();
        $abraj = Minute::factory()
            ->ofType('weekly_meeting', 'abraj_kudanah')
            ->create();

        Livewire::test(ListMinutes::class)
            ->filterTable('sites', 'site_a')
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$abraj]);
    }

    public function test_site_is_required_for_asset_tagging(): void
    {
        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => 'asset_tagging',
                'sites' => null,
                'title' => 'محضر تسليم علامات ترميز',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('minute.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites' => 'required']);
    }

    public function test_asset_tagging_rejects_abraj_kudanah_site(): void
    {
        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => 'asset_tagging',
                'sites' => ['abraj_kudanah'],
                'title' => 'محضر تسليم علامات ترميز',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('minute.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites']);
    }

    public function test_can_create_project_handover_without_site(): void
    {
        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => 'project_handover',
                'title' => 'محضر تسليم مشروع',
                'parties' => 'شركة الراجحي',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('minute.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(Minute::class, [
            'title' => 'محضر تسليم مشروع',
            'type' => 'project_handover',
            'sites' => null,
        ]);

        $minute = Minute::query()->firstOrFail();

        Storage::disk('local')->assertExists($minute->file_path);
        $this->assertMatchesRegularExpression('/^محضر-\d{4}-\d{4}$/', $minute->reference_number);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => null,
                'title' => null,
                'document_date' => null,
                'file_path' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'type' => 'required',
                'title' => 'required',
                'document_date' => 'required',
                'file_path' => 'required',
            ]);
    }
}
