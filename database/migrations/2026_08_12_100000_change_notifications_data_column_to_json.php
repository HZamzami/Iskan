<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel's default notifications migration types `data` as `text`, which
     * works on MySQL/MariaDB but breaks on PostgreSQL: Filament's database
     * notifications queries use the `->>'key'` JSON operator, which Postgres
     * only defines for `json`/`jsonb` columns, not `text`.
     *
     * Postgres has no automatic assignment cast from `text` to `json`, so
     * Laravel's grammar-generated `ALTER COLUMN ... TYPE json` (no `USING`
     * clause) fails outright — an explicit cast is required on that driver.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('alter table notifications alter column data type json using data::json');

            return;
        }

        Schema::table('notifications', function (Blueprint $table): void {
            $table->json('data')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('alter table notifications alter column data type text');

            return;
        }

        Schema::table('notifications', function (Blueprint $table): void {
            $table->text('data')->change();
        });
    }
};
