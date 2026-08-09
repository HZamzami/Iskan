<?php

namespace Tests\Feature;

use App\Filament\Resources\ContractualRequirements\Pages\CreateContractualRequirement;
use App\Filament\Resources\ContractualRequirements\Pages\ListContractualRequirements;
use App\Models\ContractualRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ContractualRequirementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_list_page_shows_contractual_requirements(): void
    {
        $requirements = ContractualRequirement::factory()->count(3)->create();

        Livewire::test(ListContractualRequirements::class)
            ->assertCanSeeTableRecords($requirements);
    }

    public function test_group_tab_filters_records(): void
    {
        $laborCount = ContractualRequirement::factory()
            ->ofType('labor_count', 'site_a')
            ->create();
        $qualityPlan = ContractualRequirement::factory()
            ->ofType('quality_plan', 'site_b')
            ->create();

        Livewire::test(ListContractualRequirements::class)
            ->set('activeTab', 'monthly_counts')
            ->assertCanSeeTableRecords([$laborCount])
            ->assertCanNotSeeTableRecords([$qualityPlan]);
    }

    public function test_type_filter_narrows_to_exact_type(): void
    {
        $laborCount = ContractualRequirement::factory()
            ->ofType('labor_count', 'site_a')
            ->create();
        $equipmentCount = ContractualRequirement::factory()
            ->ofType('equipment_count', 'site_a')
            ->create();

        Livewire::test(ListContractualRequirements::class)
            ->filterTable('type', 'labor_count')
            ->assertCanSeeTableRecords([$laborCount])
            ->assertCanNotSeeTableRecords([$equipmentCount]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = ContractualRequirement::factory()
            ->ofType('labor_count', 'site_a')
            ->create();
        $siteB = ContractualRequirement::factory()
            ->ofType('labor_count', 'site_b')
            ->create();

        Livewire::test(ListContractualRequirements::class)
            ->filterTable('sites', 'site_a')
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$siteB]);
    }

    public function test_site_is_required_for_labor_count(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => 'labor_count',
                'sites' => null,
                'title' => 'حصر العمالة لشهر يوليو',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('req.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites' => 'required']);
    }

    public function test_abraj_site_is_rejected_for_management_plan(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => 'quality_plan',
                'sites' => ['abraj_kudanah'],
                'title' => 'خطة إدارة الجودة',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('req.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites']);
    }

    public function test_can_create_management_plan_for_camp_site(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => 'quality_plan',
                'sites' => ['site_b'],
                'title' => 'خطة إدارة الجودة - موقع (ب)',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('req.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(ContractualRequirement::class, [
            'title' => 'خطة إدارة الجودة - موقع (ب)',
            'type' => 'quality_plan',
        ]);

        $requirement = ContractualRequirement::query()->firstOrFail();

        $this->assertSame(['site_b'], $requirement->sites);
        Storage::disk('local')->assertExists($requirement->file_path);
        $this->assertMatchesRegularExpression('/^متطلب-\d{4}-\d{4}$/', $requirement->reference_number);
    }

    public function test_labor_count_rejects_word_file(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => 'labor_count',
                'sites' => ['site_a'],
                'title' => 'حصر العمالة لشهر يوليو',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('req.docx', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_can_create_sop_with_word_file(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => 'sop',
                'sites' => ['site_a'],
                'title' => 'إجراءات التشغيل الموحد',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('sop.docx', 100),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $requirement = ContractualRequirement::query()->where('title', 'إجراءات التشغيل الموحد')->firstOrFail();

        Storage::disk('local')->assertExists($requirement->file_path);
    }

    public function test_can_create_master_plan_with_excel_file(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => 'master_plan',
                'sites' => ['site_a'],
                'title' => 'الجداول الزمنية',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('plan.xlsx', 100),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $requirement = ContractualRequirement::query()->where('title', 'الجداول الزمنية')->firstOrFail();

        Storage::disk('local')->assertExists($requirement->file_path);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreateContractualRequirement::class)
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
