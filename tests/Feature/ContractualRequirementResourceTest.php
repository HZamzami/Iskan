<?php

namespace Tests\Feature;

use App\Enums\ContractualRequirementType;
use App\Enums\RequirementGroup;
use App\Enums\Site;
use App\Filament\Resources\ContractualRequirements\Pages\CreateContractualRequirement;
use App\Filament\Resources\ContractualRequirements\Pages\ListContractualRequirements;
use App\Models\ContractualRequirement;
use App\Models\User;
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

        $this->actingAs(User::factory()->create());
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
            ->ofType(ContractualRequirementType::LaborCount, Site::SiteA)
            ->create();
        $qualityPlan = ContractualRequirement::factory()
            ->ofType(ContractualRequirementType::QualityPlan, Site::SiteB)
            ->create();

        Livewire::test(ListContractualRequirements::class)
            ->set('activeTab', RequirementGroup::MonthlyCounts->value)
            ->assertCanSeeTableRecords([$laborCount])
            ->assertCanNotSeeTableRecords([$qualityPlan]);
    }

    public function test_type_filter_narrows_to_exact_type(): void
    {
        $laborCount = ContractualRequirement::factory()
            ->ofType(ContractualRequirementType::LaborCount, Site::SiteA)
            ->create();
        $equipmentCount = ContractualRequirement::factory()
            ->ofType(ContractualRequirementType::EquipmentCount, Site::SiteA)
            ->create();

        Livewire::test(ListContractualRequirements::class)
            ->filterTable('type', ContractualRequirementType::LaborCount->value)
            ->assertCanSeeTableRecords([$laborCount])
            ->assertCanNotSeeTableRecords([$equipmentCount]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = ContractualRequirement::factory()
            ->ofType(ContractualRequirementType::LaborCount, Site::SiteA)
            ->create();
        $siteB = ContractualRequirement::factory()
            ->ofType(ContractualRequirementType::LaborCount, Site::SiteB)
            ->create();

        Livewire::test(ListContractualRequirements::class)
            ->filterTable('site', Site::SiteA->value)
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$siteB]);
    }

    public function test_site_is_required_for_labor_count(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => ContractualRequirementType::LaborCount->value,
                'site' => null,
                'title' => 'حصر العمالة لشهر يوليو',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('req.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['site' => 'required']);
    }

    public function test_abraj_site_is_rejected_for_management_plan(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => ContractualRequirementType::QualityPlan->value,
                'site' => Site::AbrajKudanah->value,
                'title' => 'خطة إدارة الجودة',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('req.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['site']);
    }

    public function test_can_create_management_plan_for_camp_site(): void
    {
        Livewire::test(CreateContractualRequirement::class)
            ->fillForm([
                'type' => ContractualRequirementType::QualityPlan->value,
                'site' => Site::SiteB->value,
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
            'type' => ContractualRequirementType::QualityPlan->value,
            'site' => Site::SiteB->value,
        ]);

        $requirement = ContractualRequirement::query()->firstOrFail();

        Storage::disk('local')->assertExists($requirement->file_path);
        $this->assertMatchesRegularExpression('/^متطلب-\d{4}-\d{4}$/', $requirement->reference_number);
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
