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
        Schema::table('periodic_reports', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('workflow_status')->nullable()->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_entity_type_id')->nullable()->constrained('entity_types')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->index(['assigned_to', 'workflow_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodic_reports', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'workflow_status']);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('assigned_entity_type_id');
            $table->dropColumn(['workflow_status', 'completed_at']);
        });
    }
};
