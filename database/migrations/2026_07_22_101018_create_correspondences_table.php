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
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('subject');
            $table->string('direction')->index();
            $table->string('status')->default('new')->index();
            $table->string('sender');
            $table->string('recipient');
            $table->foreignId('entity_id')->constrained()->restrictOnDelete();
            $table->date('document_date');
            $table->string('file_path');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondences');
    }
};
