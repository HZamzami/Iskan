<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\ContractDocumentType;
use App\Enums\Module;
use App\Enums\Site;
use App\Filament\Resources\ContractDocuments\Pages\ListContractDocuments;
use App\Filament\Resources\Minutes\Pages\ListMinutes;
use App\Models\ContractDocument;
use App\Models\Minute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_user_without_module_access_cannot_view_list_page(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Read,
        ]));

        $this->assertFalse(auth()->user()->can('viewAny', ContractDocument::class));

        Livewire::test(ListContractDocuments::class)
            ->assertForbidden();
    }

    public function test_read_level_allows_viewing_but_not_creating(): void
    {
        $user = $this->makeUserWithAccess([
            Module::ContractDocuments->value => AccessLevel::Read,
        ]);
        $this->actingAs($user);

        $document = ContractDocument::factory()->create();

        $this->assertTrue($user->can('viewAny', ContractDocument::class));
        $this->assertTrue($user->can('view', $document));
        $this->assertFalse($user->can('create', ContractDocument::class));
        $this->assertFalse($user->can('update', $document));
        $this->assertFalse($user->can('delete', $document));
    }

    public function test_write_level_allows_creating_but_not_updating(): void
    {
        $user = $this->makeUserWithAccess([
            Module::ContractDocuments->value => AccessLevel::Write,
        ]);

        $document = ContractDocument::factory()->create();

        $this->assertTrue($user->can('viewAny', ContractDocument::class));
        $this->assertTrue($user->can('create', ContractDocument::class));
        $this->assertFalse($user->can('update', $document));
        $this->assertFalse($user->can('delete', $document));
    }

    public function test_edit_level_allows_updating_but_not_deleting(): void
    {
        $user = $this->makeUserWithAccess([
            Module::ContractDocuments->value => AccessLevel::Edit,
        ]);

        $document = ContractDocument::factory()->create();

        $this->assertTrue($user->can('viewAny', ContractDocument::class));
        $this->assertTrue($user->can('create', ContractDocument::class));
        $this->assertTrue($user->can('update', $document));
        $this->assertFalse($user->can('delete', $document));
    }

    public function test_delete_level_allows_everything(): void
    {
        $user = $this->makeUserWithAccess([
            Module::ContractDocuments->value => AccessLevel::Delete,
        ]);

        $document = ContractDocument::factory()->create();

        $this->assertTrue($user->can('viewAny', ContractDocument::class));
        $this->assertTrue($user->can('create', ContractDocument::class));
        $this->assertTrue($user->can('update', $document));
        $this->assertTrue($user->can('delete', $document));
    }

    public function test_admin_has_full_access_everywhere(): void
    {
        $admin = $this->makeAdminUser();

        $document = ContractDocument::factory()->create();

        $this->assertTrue($admin->can('viewAny', ContractDocument::class));
        $this->assertTrue($admin->can('create', ContractDocument::class));
        $this->assertTrue($admin->can('update', $document));
        $this->assertTrue($admin->can('delete', $document));
        $this->assertNull($admin->allowedSites());
    }

    public function test_site_restricted_user_sees_only_allowed_and_general_records(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [Module::ContractDocuments->value => AccessLevel::Read],
            [Site::SiteA],
        ));

        $siteA = ContractDocument::factory()
            ->ofType(ContractDocumentType::OperationContract, Site::SiteA)
            ->create();
        $siteB = ContractDocument::factory()
            ->ofType(ContractDocumentType::OperationContract, Site::SiteB)
            ->create();
        $general = ContractDocument::factory()
            ->ofType(ContractDocumentType::ConsultantContract)
            ->create();

        Livewire::test(ListContractDocuments::class)
            ->assertCanSeeTableRecords([$siteA, $general])
            ->assertCanNotSeeTableRecords([$siteB]);
    }

    public function test_site_restriction_blocks_record_level_actions(): void
    {
        $user = $this->makeUserWithAccess(
            [Module::ContractDocuments->value => AccessLevel::Edit],
            [Site::SiteA],
        );

        $siteB = ContractDocument::factory()
            ->ofType(ContractDocumentType::OperationContract, Site::SiteB)
            ->create();
        $general = ContractDocument::factory()
            ->ofType(ContractDocumentType::ConsultantContract)
            ->create();

        $this->assertFalse($user->can('view', $siteB));
        $this->assertFalse($user->can('update', $siteB));
        $this->assertFalse($user->can('delete', $siteB));
        $this->assertTrue($user->can('update', $general));
    }

    public function test_user_with_all_sites_selected_sees_all_sites(): void
    {
        $this->actingAs($this->makeUserWithAccess(
            [Module::Minutes->value => AccessLevel::Read],
            Site::cases(),
        ));

        $minutes = collect(Site::cases())
            ->map(fn (Site $site) => Minute::factory()->create(['sites' => [$site]]));

        Livewire::test(ListMinutes::class)
            ->assertCanSeeTableRecords($minutes);
    }

    public function test_user_with_no_sites_selected_sees_only_general_records(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Read,
        ]));

        $siteMinutes = collect(Site::cases())
            ->map(fn (Site $site) => Minute::factory()->create(['sites' => [$site]]));
        $general = Minute::factory()->create(['sites' => null]);

        Livewire::test(ListMinutes::class)
            ->assertCanSeeTableRecords([$general])
            ->assertCanNotSeeTableRecords($siteMinutes);
    }

    public function test_cascade_covers_lower_levels(): void
    {
        $this->assertTrue(AccessLevel::Edit->covers(AccessLevel::Read));
        $this->assertTrue(AccessLevel::Edit->covers(AccessLevel::Write));
        $this->assertTrue(AccessLevel::Write->covers(AccessLevel::Read));
        $this->assertTrue(AccessLevel::Delete->covers(AccessLevel::Edit));
        $this->assertTrue(AccessLevel::Delete->covers(AccessLevel::Write));
        $this->assertTrue(AccessLevel::Delete->covers(AccessLevel::Read));
        $this->assertFalse(AccessLevel::Read->covers(AccessLevel::Write));
        $this->assertFalse(AccessLevel::Write->covers(AccessLevel::Edit));
        $this->assertFalse(AccessLevel::Edit->covers(AccessLevel::Delete));
    }
}
