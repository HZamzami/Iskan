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
        Schema::create('contractual_requirement_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_label')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->foreignId('requirement_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('site_scope')->default('all')->index();
            $table->json('sites')->nullable();
            $table->json('accepted_extensions')->nullable();
            $table->unsignedInteger('max_file_size')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contractual_requirement_types');
    }
};
