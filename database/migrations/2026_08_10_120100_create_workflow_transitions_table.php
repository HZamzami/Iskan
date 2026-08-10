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
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->morphs('transitionable');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('action');
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('entity_type_id')->nullable()->constrained('entity_types')->nullOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_transitions');
    }
};
