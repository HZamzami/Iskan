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
        Schema::create('financial_flow_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_label')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->default('gray');
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
        Schema::dropIfExists('financial_flow_types');
    }
};
