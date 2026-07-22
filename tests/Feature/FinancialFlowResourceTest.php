<?php

namespace Tests\Feature;

use App\Enums\FinancialFlowType;
use App\Enums\Site;
use App\Filament\Resources\FinancialFlows\Pages\CreateFinancialFlow;
use App\Filament\Resources\FinancialFlows\Pages\ListFinancialFlows;
use App\Models\FinancialFlow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialFlowResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->actingAs(User::factory()->create());
    }

    public function test_list_page_shows_financial_flows(): void
    {
        $flows = FinancialFlow::factory()->count(3)->create();

        Livewire::test(ListFinancialFlows::class)
            ->assertCanSeeTableRecords($flows);
    }

    public function test_type_tab_filters_records(): void
    {
        $consultant = FinancialFlow::factory()
            ->ofType(FinancialFlowType::Consultant)
            ->create();
        $operation = FinancialFlow::factory()
            ->ofType(FinancialFlowType::Operation, Site::SiteA)
            ->create();

        Livewire::test(ListFinancialFlows::class)
            ->set('activeTab', FinancialFlowType::Consultant->value)
            ->assertCanSeeTableRecords([$consultant])
            ->assertCanNotSeeTableRecords([$operation]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = FinancialFlow::factory()
            ->ofType(FinancialFlowType::Operation, Site::SiteA)
            ->create();
        $abraj = FinancialFlow::factory()
            ->ofType(FinancialFlowType::Operation, Site::AbrajKudanah)
            ->create();

        Livewire::test(ListFinancialFlows::class)
            ->filterTable('site', Site::SiteA->value)
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$abraj]);
    }

    public function test_site_is_required_for_operation_flow(): void
    {
        Livewire::test(CreateFinancialFlow::class)
            ->fillForm([
                'type' => FinancialFlowType::Operation->value,
                'site' => null,
                'title' => 'تدفق مالي لعقد الصيانة والتشغيل',
                'period_month' => '2026-07-01',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('flow.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['site' => 'required']);
    }

    public function test_can_create_consultant_flow_without_site(): void
    {
        Livewire::test(CreateFinancialFlow::class)
            ->fillForm([
                'type' => FinancialFlowType::Consultant->value,
                'title' => 'تدفق مالي لعقد الإستشاري',
                'period_month' => '2026-07-01',
                'amount' => 150000,
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('flow.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(FinancialFlow::class, [
            'title' => 'تدفق مالي لعقد الإستشاري',
            'type' => FinancialFlowType::Consultant->value,
            'site' => null,
            'amount' => 150000,
        ]);

        $flow = FinancialFlow::query()->firstOrFail();

        Storage::disk('local')->assertExists($flow->file_path);
        $this->assertMatchesRegularExpression('/^تدفق-\d{4}-\d{4}$/', $flow->reference_number);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreateFinancialFlow::class)
            ->fillForm([
                'type' => null,
                'title' => null,
                'period_month' => null,
                'document_date' => null,
                'file_path' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'type' => 'required',
                'title' => 'required',
                'period_month' => 'required',
                'document_date' => 'required',
                'file_path' => 'required',
            ]);
    }
}
