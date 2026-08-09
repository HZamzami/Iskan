<?php

namespace Tests\Feature;

use App\Filament\Resources\ContractDocuments\Pages\CreateContractDocument;
use App\Filament\Resources\ContractDocuments\Pages\ListContractDocuments;
use App\Models\ContractDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ContractDocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_list_page_shows_contract_documents(): void
    {
        $documents = ContractDocument::factory()->count(3)->create();

        Livewire::test(ListContractDocuments::class)
            ->assertCanSeeTableRecords($documents);
    }

    public function test_type_tab_filters_records(): void
    {
        $consultant = ContractDocument::factory()
            ->ofType('consultant_contract')
            ->create();
        $operation = ContractDocument::factory()
            ->ofType('operation_contract', 'site_a')
            ->create();

        Livewire::test(ListContractDocuments::class)
            ->set('activeTab', 'consultant_contract')
            ->assertCanSeeTableRecords([$consultant])
            ->assertCanNotSeeTableRecords([$operation]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = ContractDocument::factory()
            ->ofType('operation_contract', 'site_a')
            ->create();
        $abraj = ContractDocument::factory()
            ->ofType('operation_contract', 'abraj_kudanah')
            ->create();

        Livewire::test(ListContractDocuments::class)
            ->filterTable('sites', 'site_a')
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$abraj]);
    }

    public function test_site_is_required_for_operation_contract(): void
    {
        Livewire::test(CreateContractDocument::class)
            ->fillForm([
                'type' => 'operation_contract',
                'sites' => null,
                'title' => 'عقد صيانة وتشغيل',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites' => 'required']);
    }

    public function test_can_create_consultant_contract_without_site(): void
    {
        Livewire::test(CreateContractDocument::class)
            ->fillForm([
                'type' => 'consultant_contract',
                'title' => 'عقد الإستشاري',
                'contracting_party' => 'إيهاف',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(ContractDocument::class, [
            'title' => 'عقد الإستشاري',
            'type' => 'consultant_contract',
            'sites' => null,
        ]);

        $document = ContractDocument::query()->firstOrFail();

        Storage::disk('local')->assertExists($document->file_path);
        $this->assertMatchesRegularExpression('/^عقد-\d{4}-\d{4}$/', $document->reference_number);
    }

    public function test_can_create_operation_contract_for_abraj(): void
    {
        Livewire::test(CreateContractDocument::class)
            ->fillForm([
                'type' => 'operation_contract',
                'sites' => ['abraj_kudanah'],
                'title' => 'عقد صيانة أبراج كدانة',
                'contracting_party' => 'شركة الراجحي',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(ContractDocument::class, [
            'title' => 'عقد صيانة أبراج كدانة',
        ]);

        $document = ContractDocument::query()->where('title', 'عقد صيانة أبراج كدانة')->firstOrFail();

        $this->assertSame(['abraj_kudanah'], $document->sites);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreateContractDocument::class)
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
