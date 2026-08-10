<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedAccessControl();
    }

    public function test_the_three_seeded_roles_have_stable_slugs(): void
    {
        $this->assertSame(
            ['contractor', 'consultant', 'owner'],
            Role::cached()->pluck('slug')->all(),
        );
    }

    public function test_renaming_a_role_does_not_change_its_slug(): void
    {
        $consultant = Role::query()->where('slug', 'consultant')->firstOrFail();

        $consultant->update(['name' => 'استشاري رئيسي']);

        $this->assertSame('consultant', $consultant->fresh()->slug);
    }

    public function test_user_category_resolves_through_role(): void
    {
        $role = Role::query()->where('slug', 'owner')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertSame('owner', $user->category());
        $this->assertTrue($user->fresh()->is($role->fresh()->users->firstOrFail()));
    }

    public function test_user_category_is_null_without_a_role(): void
    {
        $user = User::factory()->create(['role_id' => null]);

        $this->assertNull($user->category());
    }

    public function test_of_category_scope_filters_users_by_role_slug(): void
    {
        $consultantRole = Role::query()->where('slug', 'consultant')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();

        $consultant = User::factory()->create(['role_id' => $consultantRole->id]);
        User::factory()->create(['role_id' => $ownerRole->id]);
        User::factory()->create(['role_id' => null]);

        $result = User::query()->ofCategory('consultant')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($consultant));
    }
}
