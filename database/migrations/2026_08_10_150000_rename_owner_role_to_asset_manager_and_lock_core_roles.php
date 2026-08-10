<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles_lookup')
            ->where('slug', 'owner')
            ->update(['name' => 'مدير الأصل', 'slug' => 'asset_manager', 'updated_at' => now()]);

        // زرع الأدوار الأساسية الثلاثة هنا (وليس فقط في RoleSeeder) لضمان
        // وجودها بعد كل نشر على الإنتاج تلقائياً، حيث تُطبَّق الترحيلات فقط
        // دون تشغيل الـ Seeders.
        $coreRows = [
            ['slug' => 'contractor', 'name' => 'مقاول', 'sort_order' => 0],
            ['slug' => 'consultant', 'name' => 'استشاري', 'sort_order' => 1],
            ['slug' => 'asset_manager', 'name' => 'مدير الأصل', 'sort_order' => 2],
        ];

        foreach ($coreRows as $row) {
            if (! DB::table('roles_lookup')->where('slug', $row['slug'])->exists()) {
                DB::table('roles_lookup')->insert([
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'is_active' => true,
                    'sort_order' => $row['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles_lookup')
            ->where('slug', 'asset_manager')
            ->update(['name' => 'مالك', 'slug' => 'owner', 'updated_at' => now()]);
    }
};
