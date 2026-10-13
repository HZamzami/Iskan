<?php

namespace Tests\Feature;

use App\Filament\Resources\Minutes\Pages\CreateMinute;
use App\Filament\Resources\MinuteTypes\Pages\ManageMinuteTypes;
use App\Models\Minute;
use App\Models\MinuteType;
use App\Support\FileSize;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class FileSizeLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_converts_between_kilobytes_and_megabytes(): void
    {
        $this->assertSame(20.0, FileSize::kbToMb(20480));
        $this->assertSame(2.5, FileSize::kbToMb(2560));
        $this->assertSame(5120, FileSize::mbToKb(5));
        $this->assertSame(1536, FileSize::mbToKb(1.5));
        $this->assertSame(1, FileSize::mbToKb(0.0001));
    }

    public function test_formats_a_readable_megabyte_label(): void
    {
        $this->assertSame('20 ميجابايت', FileSize::label(20480));
        $this->assertSame('2.5 ميجابايت', FileSize::label(2560));
        $this->assertSame('50 ميجابايت', FileSize::label(51200));
    }

    public function test_admin_enters_max_file_size_in_megabytes_and_it_is_stored_in_kilobytes(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(ManageMinuteTypes::class)
            ->callAction('create', data: [
                'name' => 'نوع بحد ٥ ميجابايت',
                'color' => 'success',
                'site_scope' => 'all',
                'max_file_size' => 5,
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $type = MinuteType::query()->where('name', 'نوع بحد ٥ ميجابايت')->firstOrFail();

        $this->assertSame(5120, $type->max_file_size);
        $this->assertSame(5120, $type->maxFileSizeKb());
    }

    public function test_editing_a_type_shows_its_limit_in_megabytes_and_keeps_it_on_save(): void
    {
        $this->actingAs($this->makeAdminUser());

        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $type->update(['max_file_size' => 10240]);

        Livewire::test(ManageMinuteTypes::class)
            ->mountAction(TestAction::make('edit')->table($type))
            ->assertSchemaStateSet(['max_file_size' => 10.0])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame(10240, $type->fresh()->max_file_size);
    }

    public function test_clearing_the_limit_falls_back_to_the_default(): void
    {
        $this->actingAs($this->makeAdminUser());

        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $type->update(['max_file_size' => 10240]);

        Livewire::test(ManageMinuteTypes::class)
            ->callAction(TestAction::make('edit')->table($type), [
                'max_file_size' => null,
            ])
            ->assertHasNoActionErrors();

        $this->assertNull($type->fresh()->max_file_size);
        $this->assertSame(20480, $type->fresh()->maxFileSizeKb());
    }

    public function test_upload_over_the_type_limit_is_rejected_with_a_megabyte_message(): void
    {
        Storage::fake('local');
        $this->actingAs($this->makeAdminUser());

        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $type->update(['max_file_size' => 1024, 'accepted_extensions' => ['pdf']]);

        Livewire::test(CreateMinute::class)
            ->fillForm([
                'type' => $type->slug,
                'sites' => ['site_a'],
                'title' => 'محضر بملف كبير',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('big.pdf', 2048, 'application/pdf'),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path' => 'يجب ألا يتجاوز حجم ملف المستند 1 ميجابايت.']);

        $this->assertDatabaseMissing(Minute::class, ['title' => 'محضر بملف كبير']);
    }

    public function test_file_max_validation_message_is_in_megabytes(): void
    {
        $validator = Validator::make(
            ['file' => UploadedFile::fake()->create('big.pdf', 30000)],
            ['file' => 'file|max:20480'],
        );

        $message = $validator->errors()->first('file');

        $this->assertStringContainsString('20 ميجابايت', $message);
        $this->assertStringNotContainsString('كيلوبايت', $message);
        $this->assertStringNotContainsString(':max', $message);
    }

    public function test_non_file_max_messages_are_unchanged(): void
    {
        $validator = Validator::make(['title' => str_repeat('a', 300)], ['title' => 'string|max:255']);

        $this->assertStringContainsString('255', $validator->errors()->first('title'));
    }

    public function test_upload_helper_text_shows_the_limit_in_megabytes(): void
    {
        $this->actingAs($this->makeAdminUser());

        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $type->update(['max_file_size' => 5120]);

        Livewire::test(CreateMinute::class)
            ->fillForm(['type' => $type->slug])
            ->assertSee('الحد الأقصى للحجم: 5 ميجابايت');
    }
}
