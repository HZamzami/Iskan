<?php

namespace Tests\Feature;

use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityTypeCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedAccessControl();
    }

    public function test_the_three_seeded_entity_types_have_stable_slugs(): void
    {
        $this->assertSame(
            ['contractor', 'consultant', 'owner'],
            EntityType::cached()->pluck('slug')->all(),
        );
    }

    public function test_renaming_an_entity_type_does_not_change_its_slug(): void
    {
        $consultant = EntityType::query()->where('slug', 'consultant')->firstOrFail();

        $consultant->update(['name' => 'استشاري رئيسي']);

        $this->assertSame('consultant', $consultant->fresh()->slug);
    }

    public function test_user_category_resolves_through_entity_and_entity_type(): void
    {
        $entity = Entity::factory()->create([
            'entity_type_id' => EntityType::query()->where('slug', 'owner')->firstOrFail()->id,
        ]);
        $user = User::factory()->create(['entity_id' => $entity->id]);

        $this->assertSame('owner', $user->category());
        $this->assertTrue($user->fresh()->is($entity->fresh()->users->firstOrFail()));
    }

    public function test_user_category_is_null_without_an_entity(): void
    {
        $user = User::factory()->create(['entity_id' => null]);

        $this->assertNull($user->category());
    }

    public function test_of_category_scope_filters_users_by_entity_type_slug(): void
    {
        $consultantEntity = Entity::factory()->create([
            'entity_type_id' => EntityType::query()->where('slug', 'consultant')->firstOrFail()->id,
        ]);
        $ownerEntity = Entity::factory()->create([
            'entity_type_id' => EntityType::query()->where('slug', 'owner')->firstOrFail()->id,
        ]);

        $consultant = User::factory()->create(['entity_id' => $consultantEntity->id]);
        User::factory()->create(['entity_id' => $ownerEntity->id]);
        User::factory()->create(['entity_id' => null]);

        $result = User::query()->ofCategory('consultant')->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($consultant));
    }
}
