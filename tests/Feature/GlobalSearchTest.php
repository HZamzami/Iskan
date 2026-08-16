<?php

namespace Tests\Feature;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Correspondence;
use App\Models\Minute;
use App\Models\Task;
use Filament\Livewire\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_and_archive_resources_declare_searchable_attributes(): void
    {
        $this->assertSame(['title'], TaskResource::getGloballySearchableAttributes());
        $this->assertSame(['reference_number', 'title'], ContractDocumentResource::getGloballySearchableAttributes());
        $this->assertSame(['reference_number', 'title'], ContractualRequirementResource::getGloballySearchableAttributes());
        $this->assertSame(['reference_number', 'title'], FinancialFlowResource::getGloballySearchableAttributes());
        $this->assertSame(['reference_number', 'title'], PeriodicReportResource::getGloballySearchableAttributes());
        $this->assertSame(['reference_number', 'title'], GeoDocumentResource::getGloballySearchableAttributes());
        $this->assertSame(['reference_number', 'subject'], CorrespondenceResource::getGloballySearchableAttributes());
    }

    public function test_global_search_result_title_combines_reference_number_and_title_or_subject(): void
    {
        $correspondence = Correspondence::factory()->create(['subject' => 'خطاب اختباري']);

        $this->assertSame(
            "{$correspondence->reference_number} — خطاب اختباري",
            CorrespondenceResource::getGlobalSearchResultTitle($correspondence),
        );
    }

    public function test_global_search_finds_a_task_by_title(): void
    {
        $this->actingAs($this->makeAdminUser());

        $task = Task::factory()->create(['title' => 'مراجعة تقرير الصيانة الفريد']);
        Task::factory()->create(['title' => 'مهمة أخرى لا علاقة لها']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'الفريد')
            ->assertSee('مراجعة تقرير الصيانة الفريد')
            ->assertDontSee('مهمة أخرى لا علاقة لها');
    }

    public function test_global_search_finds_a_minute_by_reference_number(): void
    {
        $this->actingAs($this->makeAdminUser());

        $minute = Minute::factory()->create();

        Livewire::test(GlobalSearch::class)
            ->set('search', $minute->reference_number)
            ->assertSee($minute->reference_number);
    }
}
