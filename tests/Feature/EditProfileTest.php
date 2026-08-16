<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_inside_the_full_panel_shell(): void
    {
        $this->actingAs($this->makeAdminUser());

        $this->get(route('filament.admin.auth.profile'))
            ->assertOk()
            ->assertSee('ميسر')
            ->assertSee('لوحة المعلومات');
    }

    public function test_user_can_update_name_and_phone(): void
    {
        $user = $this->makeAdminUser();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'اسم محدث',
                'phone' => '0501234567',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('اسم محدث', $user->name);
        $this->assertSame('0501234567', $user->phone);
    }

    public function test_user_can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = $this->makeAdminUser();
        $this->actingAs($user);

        $avatar = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(EditProfile::class)
            ->fillForm([
                'avatar_path' => $avatar,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
        $this->assertSame(
            Storage::disk('public')->url($user->avatar_path),
            $user->getFilamentAvatarUrl(),
        );
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = $this->makeAdminUser();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'password' => 'new-password123',
                'passwordConfirmation' => 'new-password123',
                'currentPassword' => 'password',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('new-password123', $user->refresh()->password));
    }

    public function test_user_cannot_change_password_without_correct_current_password(): void
    {
        $user = $this->makeAdminUser();
        $this->actingAs($user);

        Livewire::test(EditProfile::class)
            ->fillForm([
                'password' => 'new-password123',
                'passwordConfirmation' => 'new-password123',
                'currentPassword' => 'wrong-password',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }
}
