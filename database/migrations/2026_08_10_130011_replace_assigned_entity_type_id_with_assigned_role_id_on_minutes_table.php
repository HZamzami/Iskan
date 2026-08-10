<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('minutes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_entity_type_id');
        });

        Schema::table('minutes', function (Blueprint $table) {
            $table->foreignId('assigned_role_id')->nullable()->after('assigned_to')->constrained('roles_lookup')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('minutes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_role_id');
        });

        Schema::table('minutes', function (Blueprint $table) {
            $table->foreignId('assigned_entity_type_id')->nullable()->after('assigned_to')->constrained('entity_types')->nullOnDelete();
        });
    }
};
