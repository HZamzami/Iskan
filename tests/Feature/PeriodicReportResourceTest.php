<?php

namespace Tests\Feature;

use App\Filament\Resources\PeriodicReports\Pages\CreatePeriodicReport;
use App\Filament\Resources\PeriodicReports\Pages\ListPeriodicReports;
use App\Models\PeriodicReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PeriodicReportResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_list_page_shows_periodic_reports(): void
    {
        $reports = PeriodicReport::factory()->count(3)->create();

        Livewire::test(ListPeriodicReports::class)
            ->assertCanSeeTableRecords($reports);
    }

    public function test_type_tab_filters_records(): void
    {
        $monthly = PeriodicReport::factory()
            ->ofType('monthly_report', 'site_a')
            ->create();
        $weekly = PeriodicReport::factory()
            ->ofType('weekly_progress', 'site_b')
            ->create();

        Livewire::test(ListPeriodicReports::class)
            ->set('activeTab', 'monthly_report')
            ->assertCanSeeTableRecords([$monthly])
            ->assertCanNotSeeTableRecords([$weekly]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = PeriodicReport::factory()
            ->ofType('weekly_progress', 'site_a')
            ->create();
        $abraj = PeriodicReport::factory()
            ->ofType('weekly_progress', 'abraj_kudanah')
            ->create();

        Livewire::test(ListPeriodicReports::class)
            ->filterTable('sites', 'site_a')
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$abraj]);
    }

    public function test_site_is_required_for_monthly_report(): void
    {
        Livewire::test(CreatePeriodicReport::class)
            ->fillForm([
                'type' => 'monthly_report',
                'sites' => null,
                'title' => 'التقرير الشهري',
                'period' => '2026-07-01',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites' => 'required']);
    }

    public function test_can_create_weekly_progress_report_for_abraj(): void
    {
        Livewire::test(CreatePeriodicReport::class)
            ->fillForm([
                'type' => 'weekly_progress',
                'sites' => ['abraj_kudanah'],
                'title' => 'تقرير إنجاز الأعمال الأسبوعي',
                'period' => '2026-07-15',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(PeriodicReport::class, [
            'title' => 'تقرير إنجاز الأعمال الأسبوعي',
            'type' => 'weekly_progress',
        ]);

        $report = PeriodicReport::query()->firstOrFail();

        $this->assertSame(['abraj_kudanah'], $report->sites);
        Storage::disk('local')->assertExists($report->file_path);
        $this->assertMatchesRegularExpression('/^تقرير-\d{4}-\d{4}$/', $report->reference_number);
    }

    public function test_weekly_progress_report_rejects_word_file(): void
    {
        Livewire::test(CreatePeriodicReport::class)
            ->fillForm([
                'type' => 'weekly_progress',
                'sites' => ['abraj_kudanah'],
                'title' => 'تقرير إنجاز الأعمال الأسبوعي',
                'period' => '2026-07-15',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('report.docx', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreatePeriodicReport::class)
            ->fillForm([
                'type' => null,
                'title' => null,
                'period' => null,
                'document_date' => null,
                'file_path' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'type' => 'required',
                'title' => 'required',
                'period' => 'required',
                'document_date' => 'required',
                'file_path' => 'required',
            ]);
    }
}
