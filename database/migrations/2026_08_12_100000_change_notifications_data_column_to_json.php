<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel's default notifications migration types `data` as `text`, which
     * works on MySQL/MariaDB but breaks on PostgreSQL: Filament's database
     * notifications queries use the `->>'key'` JSON operator, which Postgres
     * only defines for `json`/`jsonb` columns, not `text`.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->json('data')->change();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->text('data')->change();
        });
    }
};
