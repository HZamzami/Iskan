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
        Schema::table('contractual_requirement_types', function (Blueprint $table) {
            $table->boolean('requires_workflow')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractual_requirement_types', function (Blueprint $table) {
            $table->dropColumn('requires_workflow');
        });
    }
};
