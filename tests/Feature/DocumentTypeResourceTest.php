<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Resources\ContractualRequirements\Pages\ListContractualRequirements;
use App\Filament\Resources\ContractualRequirementTypes\Pages\ManageContractualRequirementTypes;
use App\Filament\Resources\Minutes\Pages\CreateMinute;
use App\Filament\Resources\Minutes\Pages\ListMinutes;
use App\Filament\Resources\MinuteTypes\Pages\ManageMinuteTypes;
use App\Models\ContractualRequirementType;
use App\Models\Minute;
use App\Models\MinuteType;
use App\Models\RequirementGroup;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentTypeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_minute_types(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        Livewire::test(ManageMinuteTypes::class)
            ->assertForbidden();
    }

    public function test_admin_created_custom_scoped_type_is_immediately_usable_on_the_record_form(): void
    {
        Storage::fake('local');

        $this->actingAs($this->makeAdminUser());

        Livewire::test(ManageMinuteTypes::class)
            ->callAction('create', data: [
                'name' => 'نوع تجريبي',
                'color' => 'success',
                'site_scope' => 'custom',
                'sites' => ['site_b'],
                'accepted_extensions' => ['docx'],
                'is_active' => true,
            ])
            ->assertNotified()
            ->assertHasNoActionErrors();

        $type = MinuteType::query()->where('name', 'نوع تجريبي')->firstOrFail();

        $this->assertSame(['site_b'], $type->allowedLocations()->pluck('slug')->all());
        $this->assertSame(['docx'], $type->acceptedExtensions());

        // The new type, created purely through the admin UI, must work on the real record form with no code changes.
        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => $type->slug,
                'sites' => ['site_a'],
                'title' => 'محضر بنوع مخصص',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('minute.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites', 'file_path']);

        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => $type->slug,
                'sites' => ['site_b'],
                'title' => 'محضر بنوع مخصص',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('minute.docx', 100),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Minute::class, ['type' => $type->slug]);
    }

    public function test_deactivating_a_type_hides_it_from_new_records_but_not_existing_ones(): void
    {
        $this->actingAs($this->makeAdminUser());

        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $minute = Minute::factory()->ofType('weekly_meeting', 'site_a')->create();

        Livewire::test(ManageMinuteTypes::class)
            ->callAction(TestAction::make('edit')->table($type), [
                'is_active' => false,
            ])
            ->assertHasNoActionErrors();

        $this->assertArrayNotHasKey($type->slug, MinuteType::selectOptions());

        Livewire::test(ListMinutes::class)
            ->assertCanSeeTableRecords([$minute]);
    }

    public function test_deleting_a_type_in_use_is_blocked_for_single_and_bulk_delete(): void
    {
        $this->actingAs($this->makeAdminUser());

        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        Minute::factory()->ofType('weekly_meeting', 'site_a')->create();

        Livewire::test(ManageMinuteTypes::class)
            ->callAction(TestAction::make('delete')->table($type))
            ->assertNotified();

        $this->assertDatabaseHas('minute_types', ['id' => $type->id]);

        Livewire::test(ManageMinuteTypes::class)
            ->callAction(TestAction::make('delete')->table($type)->bulk())
            ->assertNotified();

        $this->assertDatabaseHas('minute_types', ['id' => $type->id]);
    }

    public function test_new_contractual_requirement_type_appears_under_its_group_tab(): void
    {
        $this->actingAs($this->makeAdminUser());

        $group = RequirementGroup::query()->where('slug', 'monthly_counts')->firstOrFail();

        Livewire::test(ManageContractualRequirementTypes::class)
            ->callAction('create', data: [
                'name' => 'متطلب تجريبي',
                'requirement_group_id' => $group->id,
                'site_scope' => 'all',
                'is_active' => true,
            ])
            ->assertNotified()
            ->assertHasNoActionErrors();

        $type = ContractualRequirementType::query()->where('name', 'متطلب تجريبي')->firstOrFail();
        $this->assertTrue($type->requirementGroup->is($group));

        $tabs = Livewire::test(ListContractualRequirements::class)->instance()->getTabs();

        $this->assertArrayHasKey($group->slug, $tabs);
        $this->assertSame($group->name, $tabs[$group->slug]->getLabel());
    }
}
