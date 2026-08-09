<?php

namespace Tests\Feature;

use App\Filament\Resources\GeoDocuments\Pages\CreateGeoDocument;
use App\Filament\Resources\GeoDocuments\Pages\ListGeoDocuments;
use App\Models\GeoDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GeoDocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_list_page_shows_geo_documents(): void
    {
        $documents = GeoDocument::factory()->count(3)->create();

        Livewire::test(ListGeoDocuments::class)
            ->assertCanSeeTableRecords($documents);
    }

    public function test_type_tab_filters_records(): void
    {
        $gis = GeoDocument::factory()
            ->ofType('gis', 'site_a')
            ->create();
        $asBuilt = GeoDocument::factory()
            ->ofType('as_built_drawing', 'site_b')
            ->create();

        Livewire::test(ListGeoDocuments::class)
            ->set('activeTab', 'gis')
            ->assertCanSeeTableRecords([$gis])
            ->assertCanNotSeeTableRecords([$asBuilt]);
    }

    public function test_site_filter_narrows_records(): void
    {
        $siteA = GeoDocument::factory()
            ->ofType('gis', 'site_a')
            ->create();
        $abraj = GeoDocument::factory()
            ->ofType('gis', 'abraj_kudanah')
            ->create();

        Livewire::test(ListGeoDocuments::class)
            ->filterTable('sites', 'site_a')
            ->assertCanSeeTableRecords([$siteA])
            ->assertCanNotSeeTableRecords([$abraj]);
    }

    public function test_site_is_required_for_every_type(): void
    {
        Livewire::test(CreateGeoDocument::class)
            ->fillForm([
                'type' => 'gis',
                'sites' => null,
                'title' => 'خريطة GIS للموقع',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('map.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['sites' => 'required']);
    }

    public function test_can_create_as_built_drawing_with_site_and_drawing_number(): void
    {
        Livewire::test(CreateGeoDocument::class)
            ->fillForm([
                'type' => 'as_built_drawing',
                'sites' => ['site_c'],
                'title' => 'مخطط كما نُفذ للموقع (ج)',
                'drawing_number' => 'DWG-1234',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('map.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(GeoDocument::class, [
            'title' => 'مخطط كما نُفذ للموقع (ج)',
            'type' => 'as_built_drawing',
            'drawing_number' => 'DWG-1234',
        ]);

        $document = GeoDocument::query()->firstOrFail();

        $this->assertSame(['site_c'], $document->sites);
        Storage::disk('local')->assertExists($document->file_path);
        $this->assertMatchesRegularExpression('/^خريطة-\d{4}-\d{4}$/', $document->reference_number);
    }

    public function test_gis_type_rejects_pdf_file(): void
    {
        Livewire::test(CreateGeoDocument::class)
            ->fillForm([
                'type' => 'gis',
                'sites' => ['site_a'],
                'title' => 'خريطة GIS للموقع',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('map.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_can_create_gis_document_with_gpkg_file(): void
    {
        Livewire::test(CreateGeoDocument::class)
            ->fillForm([
                'type' => 'gis',
                'sites' => ['site_a'],
                'title' => 'خريطة GIS للموقع',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('map.gpkg', 100),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $document = GeoDocument::query()->where('title', 'خريطة GIS للموقع')->firstOrFail();

        Storage::disk('local')->assertExists($document->file_path);
    }

    public function test_kml_kmz_type_rejects_dwg_file(): void
    {
        Livewire::test(CreateGeoDocument::class)
            ->fillForm([
                'type' => 'kml_kmz',
                'sites' => ['site_a'],
                'title' => 'خريطة KML للموقع',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('map.dwg', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreateGeoDocument::class)
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
