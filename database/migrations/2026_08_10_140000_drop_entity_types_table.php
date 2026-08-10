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
        Schema::table('entities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entity_type_id');
        });

        Schema::dropIfExists('entity_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('entity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('entities', function (Blueprint $table) {
            $table->foreignId('entity_type_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });
    }
};
