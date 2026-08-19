<?php

namespace Tests\Feature;

use App\Enums\CorrespondenceDirection;
use App\Enums\CorrespondenceStatus;
use App\Filament\Resources\Correspondences\Pages\CreateCorrespondence;
use App\Filament\Resources\Correspondences\Pages\EditCorrespondence;
use App\Filament\Resources\Correspondences\Pages\ListCorrespondences;
use App\Models\Correspondence;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CorrespondenceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::disk('local')->put('correspondence-files/placeholder.pdf', '%PDF-1.4');

        $this->actingAs($this->makeAdminUser());
    }

    public function test_list_page_shows_correspondences(): void
    {
        $correspondences = Correspondence::factory()->count(3)->create();

        Livewire::test(ListCorrespondences::class)
            ->assertCanSeeTableRecords($correspondences);
    }

    public function test_incoming_tab_filters_records(): void
    {
        $incoming = Correspondence::factory()->incoming()->create();
        $outgoing = Correspondence::factory()->outgoing()->create();

        Livewire::test(ListCorrespondences::class)
            ->set('activeTab', 'incoming')
            ->assertCanSeeTableRecords([$incoming])
            ->assertCanNotSeeTableRecords([$outgoing]);
    }

    public function test_outgoing_tab_filters_records(): void
    {
        $incoming = Correspondence::factory()->incoming()->create();
        $outgoing = Correspondence::factory()->outgoing()->create();

        Livewire::test(ListCorrespondences::class)
            ->set('activeTab', 'outgoing')
            ->assertCanSeeTableRecords([$outgoing])
            ->assertCanNotSeeTableRecords([$incoming]);
    }

    public function test_can_create_correspondence_with_pdf(): void
    {
        $entity = Entity::factory()->create();
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        Livewire::test(CreateCorrespondence::class)
            ->fillForm([
                'direction' => CorrespondenceDirection::Incoming->value,
                'subject' => 'خطاب بشأن تخصيص أرض',
                'status' => CorrespondenceStatus::New->value,
                'sender_user_id' => $sender->id,
                'recipient_user_id' => $recipient->id,
                'entity_id' => $entity->id,
                'document_date' => '2026-07-01',
                'file_path' => $file,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas(Correspondence::class, [
            'subject' => 'خطاب بشأن تخصيص أرض',
            'direction' => CorrespondenceDirection::Incoming->value,
            'entity_id' => $entity->id,
            'sender_user_id' => $sender->id,
            'recipient_user_id' => $recipient->id,
        ]);

        $correspondence = Correspondence::query()->firstOrFail();

        Storage::disk('local')->assertExists($correspondence->file_path);
        $this->assertSame($sender->name, $correspondence->senderLabel());
        $this->assertSame($recipient->name, $correspondence->recipientLabel());
    }

    public function test_create_rejects_a_non_pdf_attachment(): void
    {
        $entity = Entity::factory()->create();

        Livewire::test(CreateCorrespondence::class)
            ->fillForm([
                'direction' => CorrespondenceDirection::Incoming->value,
                'subject' => 'خطاب بشأن تخصيص أرض',
                'status' => CorrespondenceStatus::New->value,
                'entity_id' => $entity->id,
                'document_date' => '2026-07-01',
                'file_path' => UploadedFile::fake()->create('malware.exe', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_sender_and_recipient_fall_back_to_legacy_text_for_old_records(): void
    {
        $correspondence = Correspondence::factory()->create([
            'sender' => 'وزارة الإسكان',
            'recipient' => 'إدارة المشاريع',
        ]);

        $this->assertSame('وزارة الإسكان', $correspondence->senderLabel());
        $this->assertSame('إدارة المشاريع', $correspondence->recipientLabel());
    }

    public function test_sender_user_takes_precedence_over_legacy_text(): void
    {
        $sender = User::factory()->create(['name' => 'أحمد الزهراني']);

        $correspondence = Correspondence::factory()->create([
            'sender' => 'نص قديم',
            'sender_user_id' => $sender->id,
        ]);

        $this->assertSame('أحمد الزهراني', $correspondence->senderLabel());
    }

    public function test_reference_number_is_auto_generated(): void
    {
        $correspondence = Correspondence::factory()->incoming()->create();

        $this->assertMatchesRegularExpression('/^و-\d{4}-\d{4}$/', $correspondence->reference_number);

        $outgoing = Correspondence::factory()->outgoing()->create();

        $this->assertMatchesRegularExpression('/^ص-\d{4}-\d{4}$/', $outgoing->reference_number);
    }

    public function test_manual_reference_number_is_kept(): void
    {
        $correspondence = Correspondence::factory()->create([
            'reference_number' => 'قديم-1445-0099',
        ]);

        $this->assertSame('قديم-1445-0099', $correspondence->reference_number);
    }

    public function test_create_validates_required_fields(): void
    {
        Livewire::test(CreateCorrespondence::class)
            ->fillForm([
                'direction' => null,
                'subject' => null,
                'entity_id' => null,
                'document_date' => null,
                'file_path' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'direction' => 'required',
                'subject' => 'required',
                'entity_id' => 'required',
                'document_date' => 'required',
                'file_path' => 'required',
            ]);
    }

    public function test_can_edit_correspondence(): void
    {
        $correspondence = Correspondence::factory()->create();

        Livewire::test(EditCorrespondence::class, ['record' => $correspondence->id])
            ->fillForm([
                'subject' => 'موضوع محدث',
                'status' => CorrespondenceStatus::Completed->value,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Correspondence::class, [
            'id' => $correspondence->id,
            'subject' => 'موضوع محدث',
            'status' => CorrespondenceStatus::Completed->value,
        ]);
    }

    public function test_reference_number_must_be_unique(): void
    {
        Correspondence::factory()->create(['reference_number' => 'و-2026-0001']);

        $entity = Entity::factory()->create();

        Livewire::test(CreateCorrespondence::class)
            ->fillForm([
                'reference_number' => 'و-2026-0001',
                'direction' => CorrespondenceDirection::Incoming->value,
                'subject' => 'موضوع',
                'status' => CorrespondenceStatus::New->value,
                'entity_id' => $entity->id,
                'document_date' => '2026-07-01',
                'file_path' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['reference_number' => 'unique']);
    }
}
