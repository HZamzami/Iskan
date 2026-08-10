<?php

namespace Tests\Feature;

use App\Filament\Resources\Minutes\Pages\EditMinute;
use App\Filament\Resources\Minutes\Pages\ListMinutes;
use App\Filament\Resources\Minutes\Pages\ViewMinute;
use App\Models\Minute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MinuteParticipantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::disk('local')->put('minutes-files/placeholder.pdf', '%PDF-1.4');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_editing_a_record_attaches_selected_participants(): void
    {
        $minute = Minute::factory()->create();
        $participants = User::factory()->count(2)->create();

        Livewire::test(EditMinute::class, ['record' => $minute->getKey()])
            ->fillForm([
                'participants' => $participants->pluck('id')->all(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $minute->refresh();

        $this->assertSame(
            $participants->pluck('id')->sort()->values()->all(),
            $minute->participants->pluck('id')->sort()->values()->all(),
        );
    }

    public function test_legacy_parties_text_still_displays_read_only_when_no_participants_attached(): void
    {
        $minute = Minute::factory()->create(['parties' => 'شركة الراجحي للتعهدات']);

        Livewire::test(ViewMinute::class, ['record' => $minute->getKey()])
            ->assertSee('شركة الراجحي للتعهدات');

        Livewire::test(EditMinute::class, ['record' => $minute->getKey()])
            ->assertFormFieldExists('parties');
    }

    public function test_table_search_matches_both_participants_and_legacy_parties(): void
    {
        $withParticipant = Minute::factory()->create(['parties' => null]);
        $participant = User::factory()->create(['name' => 'خالد المطيري']);
        $withParticipant->participants()->attach($participant);

        $withLegacyText = Minute::factory()->create(['parties' => 'شركة الراجحي للتعهدات']);

        Livewire::test(ListMinutes::class)
            ->searchTable('خالد المطيري')
            ->assertCanSeeTableRecords([$withParticipant])
            ->assertCanNotSeeTableRecords([$withLegacyText]);

        Livewire::test(ListMinutes::class)
            ->searchTable('شركة الراجحي')
            ->assertCanSeeTableRecords([$withLegacyText])
            ->assertCanNotSeeTableRecords([$withParticipant]);
    }
}
