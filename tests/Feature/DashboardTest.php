<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\TaskStatus;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\ArchiveOverviewStats;
use App\Filament\Widgets\ExpiringContracts;
use App\Filament\Widgets\FinancialFlowsChart;
use App\Filament\Widgets\LatestDocuments;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\SiteOverview;
use App\Filament\Widgets\TasksOverviewStats;
use App\Models\ContractDocument;
use App\Models\FinancialFlow;
use App\Models\GeoDocument;
use App\Models\Minute;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_admin(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('مرحباً،')
            ->assertSee($admin->name);
    }

    public function test_stats_widget_hidden_without_any_module_access(): void
    {
        $this->actingAs($this->makeUserWithAccess([]));

        $this->assertFalse(ArchiveOverviewStats::canView());
        $this->assertFalse(QuickActions::canView());
        $this->assertFalse(LatestDocuments::canView());
    }

    public function test_stats_widget_shows_only_accessible_module_stats(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Read,
        ]));

        $this->assertTrue(ArchiveOverviewStats::canView());

        Livewire::test(ArchiveOverviewStats::class)
            ->assertSee('وثائق هذا الشهر')
            ->assertDontSee('العقود السارية')
            ->assertDontSee('معاملات قيد المعالجة');
    }

    public function test_contract_stats_are_site_scoped(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [Module::ContractDocuments->value => AccessLevel::Read],
            ['site_a'],
        ));

        ContractDocument::factory()
            ->ofType('operation_contract', 'site_a')
            ->create(['start_date' => today()->subMonth(), 'end_date' => today()->addDays(30)]);
        ContractDocument::factory()
            ->ofType('operation_contract', 'site_b')
            ->create(['start_date' => today()->subMonth(), 'end_date' => today()->addDays(30)]);

        Livewire::test(ArchiveOverviewStats::class)
            ->assertSee('العقود السارية')
            ->assertSee('1 تنتهي خلال 90 يوماً');
    }

    public function test_site_overview_hidden_for_general_only_user_and_shown_for_admin(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Read,
        ]));

        $this->assertFalse(SiteOverview::canView());

        $this->actingAs($this->makeAdminUser());

        $this->assertTrue(SiteOverview::canView());

        Livewire::test(SiteOverview::class)
            ->assertSee('موقع (أ)')
            ->assertSee('موقع (ب)')
            ->assertSee('موقع (ج)')
            ->assertSee('أبراج كدانة الوادي');
    }

    public function test_site_overview_shows_only_allowed_sites_and_modules(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [Module::Minutes->value => AccessLevel::Read],
            ['site_a'],
        ));

        $this->assertTrue(SiteOverview::canView());

        Livewire::test(SiteOverview::class)
            ->assertSee('موقع (أ)')
            ->assertDontSee('أبراج كدانة الوادي')
            ->assertSee('المحاضر')
            ->assertDontSee('المستندات التعاقدية')
            ->assertSee('عزام الشريف')
            ->assertSee('راشد الرفاعي');
    }

    public function test_quick_actions_only_for_write_access(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Read,
        ]));

        $this->assertFalse(QuickActions::canView());

        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Write,
            Module::ContractDocuments->value => AccessLevel::Read,
        ]));

        $this->assertTrue(QuickActions::canView());

        Livewire::test(QuickActions::class)
            ->assertSee('محضر جديد')
            ->assertDontSee('مستند تعاقدي جديد');
    }

    public function test_recent_activity_admin_only(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        $this->assertFalse(RecentActivity::canView());

        $this->actingAs($this->makeAdminUser());

        $this->assertTrue(RecentActivity::canView());

        $minute = Minute::factory()->create();

        Livewire::test(RecentActivity::class)
            ->assertSee('آخر الأنشطة')
            ->assertSee('المحاضر');
    }

    public function test_filter_options_limited_to_allowed_sites(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [Module::Minutes->value => AccessLevel::Read],
            ['site_a'],
        ));

        Livewire::test(Dashboard::class)
            ->assertSee('موقع (أ)')
            ->assertDontSee('أبراج كدانة الوادي');
    }

    public function test_filter_hidden_for_general_only_user(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Read,
        ]));

        Livewire::test(Dashboard::class)
            ->assertDontSee('جميع المواقع');
    }

    public function test_page_filter_narrows_data_and_cannot_escape_allowed_sites(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [Module::ContractDocuments->value => AccessLevel::Read],
            ['site_a'],
        ));

        $siteA = ContractDocument::factory()
            ->ofType('operation_contract', 'site_a')
            ->create(['title' => 'عقد موقع أ', 'end_date' => today()->addDays(30)]);
        $siteB = ContractDocument::factory()
            ->ofType('operation_contract', 'site_b')
            ->create(['title' => 'عقد موقع ب', 'end_date' => today()->addDays(30)]);
        $general = ContractDocument::factory()
            ->ofType('consultant_contract')
            ->create(['title' => 'عقد استشاري عام', 'end_date' => today()->addDays(30)]);

        Livewire::test(ExpiringContracts::class, ['pageFilters' => ['site' => 'site_a']])
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$siteB, $general]);

        Livewire::test(ExpiringContracts::class, ['pageFilters' => ['site' => 'site_b']])
            ->assertCanSeeTableRecords([$siteA, $general])
            ->assertCanNotSeeTableRecords([$siteB]);
    }

    public function test_expiring_contracts_window_and_ordering(): void
    {
        $this->actingAs($this->makeAdminUser());

        $soon = ContractDocument::factory()->create(['title' => 'ينتهي قريباً', 'end_date' => today()->addDays(10)]);
        $later = ContractDocument::factory()->create(['title' => 'ينتهي لاحقاً', 'end_date' => today()->addDays(100)]);
        $far = ContractDocument::factory()->create(['title' => 'بعيد جداً', 'end_date' => today()->addDays(200)]);

        Livewire::test(ExpiringContracts::class)
            ->assertCanSeeTableRecords([$soon, $later], inOrder: true)
            ->assertCanNotSeeTableRecords([$far]);
    }

    public function test_latest_documents_merges_accessible_modules_only(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [
                Module::Minutes->value => AccessLevel::Read,
                Module::GeoDocuments->value => AccessLevel::Read,
            ],
            ['site_a', 'site_b', 'site_c', 'abraj_kudanah'],
        ));

        Minute::factory()->create(['title' => 'محضر للاختبار']);
        GeoDocument::factory()->create(['title' => 'خريطة للاختبار']);
        ContractDocument::factory()->create(['title' => 'عقد لا يظهر']);

        Livewire::test(LatestDocuments::class)
            ->assertSee('محضر للاختبار')
            ->assertSee('خريطة للاختبار')
            ->assertDontSee('عقد لا يظهر');
    }

    public function test_dashboard_shows_tasks_overview_stats(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        Task::factory()->create(['assigned_to' => $admin->id, 'status' => TaskStatus::Pending, 'due_date' => today()->subDay()]);
        Task::factory()->create(['assigned_to' => $admin->id, 'status' => TaskStatus::Completed, 'completed_at' => now()]);

        Livewire::test(TasksOverviewStats::class)
            ->assertSee('مهامي المعلقة')
            ->assertSee('مهام متأخرة')
            ->assertSee('أُنجزت هذا الأسبوع');

        $this->assertContains(TasksOverviewStats::class, (new Dashboard)->getWidgets());
    }

    public function test_financial_chart_renders_and_switches_year(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::FinancialFlows->value => AccessLevel::Read,
        ]));

        $this->assertTrue(FinancialFlowsChart::canView());

        FinancialFlow::factory()->create(['period_month' => now()->startOfMonth()]);

        Livewire::test(FinancialFlowsChart::class)
            ->assertSee('التدفقات المالية الشهرية')
            ->set('filter', 'previous')
            ->assertOk();
    }
}
