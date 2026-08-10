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
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entity_type_id');
        });

        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('action')->constrained('roles_lookup')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::table('workflow_transitions', function (Blueprint $table) {
            $table->foreignId('entity_type_id')->nullable()->after('action')->constrained('entity_types')->nullOnDelete();
        });
    }
};
