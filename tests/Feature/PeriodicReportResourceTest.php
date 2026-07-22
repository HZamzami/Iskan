<?php

namespace Tests\Feature;

use App\Enums\PeriodicReportType;
use App\Enums\Site;
use App\Filament\Resources\PeriodicReports\Pages\CreatePeriodicReport;
use App\Filament\Resources\PeriodicReports\Pages\ListPeriodicReports;
use App\Models\PeriodicReport;
use App\Models\User;
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

        $this->actingAs(User::factory()->create());
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
            ->ofType(PeriodicReportType::MonthlyReport, Site::SiteA)
            ->create();
        $weekly = PeriodicReport::factory()
            ->ofType(PeriodicReportType::WeeklyProgress, Site::SiteB)
            ->create();

        Livewire::test(ListPeriodicReports::class)
            ->set('activeTab', PeriodicReportType::MonthlyReport->value)
            ->assertCanSeeTableRecords([$monthly])
            ->assertCanNotSeeTableRecords([$weekly]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = PeriodicReport::factory()
            ->ofType(PeriodicReportType::WeeklyProgress, Site::SiteA)
            ->create();
        $abraj = PeriodicReport::factory()
            ->ofType(PeriodicReportType::WeeklyProgress, Site::AbrajKudanah)
            ->create();

        Livewire::test(ListPeriodicReports::class)
            ->filterTable('site', Site::SiteA->value)
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$abraj]);
    }

    public function test_site_is_required_for_monthly_report(): void
    {
        Livewire::test(CreatePeriodicReport::class)
            ->fillForm([
                'type' => PeriodicReportType::MonthlyReport->value,
                'site' => null,
                'title' => 'التقرير الشهري',
                'period' => '2026-07-01',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['site' => 'required']);
    }

    public function test_can_create_weekly_progress_report_for_abraj(): void
    {
        Livewire::test(CreatePeriodicReport::class)
            ->fillForm([
                'type' => PeriodicReportType::WeeklyProgress->value,
                'site' => Site::AbrajKudanah->value,
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
            'type' => PeriodicReportType::WeeklyProgress->value,
            'site' => Site::AbrajKudanah->value,
        ]);

        $report = PeriodicReport::query()->firstOrFail();

        Storage::disk('local')->assertExists($report->file_path);
        $this->assertMatchesRegularExpression('/^تقرير-\d{4}-\d{4}$/', $report->reference_number);
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
