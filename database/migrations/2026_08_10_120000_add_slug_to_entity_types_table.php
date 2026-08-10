<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entity_types', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->index()->after('slug');
            $table->unsignedInteger('sort_order')->default(0)->index()->after('is_active');
        });

        // الأسماء الثلاثة الوحيدة المزروعة حالياً (EntityTypeSeeder) لها معرّفات
        // مستقرة معروفة مسبقاً؛ أي اسم آخر غير متوقع يُعامَل بالتوليد القياسي
        // (نفس منطق IsLookupModel::generateUniqueSlug) بدلاً من الافتراض.
        $knownSlugs = [
            'مقاول' => 'contractor',
            'استشاري' => 'consultant',
            'مالك' => 'owner',
        ];

        foreach (DB::table('entity_types')->whereNull('slug')->get() as $row) {
            $base = $knownSlugs[$row->name] ?? (Str::slug($row->name, '_') ?: 'entity_type');
            $slug = $base;
            $suffix = 2;

            while (DB::table('entity_types')->where('slug', $slug)->exists()) {
                $slug = "{$base}_{$suffix}";
                $suffix++;
            }

            DB::table('entity_types')->where('id', $row->id)->update(['slug' => $slug]);
        }

        Schema::table('entity_types', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_types', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'is_active', 'sort_order']);
        });
    }
};
