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
        Schema::table('correspondences', function (Blueprint $table) {
            $table->foreignId('sender_user_id')->nullable()->after('sender')->constrained('users')->nullOnDelete();
            $table->foreignId('recipient_user_id')->nullable()->after('recipient')->constrained('users')->nullOnDelete();
        });

        Schema::table('correspondences', function (Blueprint $table) {
            $table->string('sender')->nullable()->change();
            $table->string('recipient')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('correspondences', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sender_user_id');
            $table->dropConstrainedForeignId('recipient_user_id');
        });

        Schema::table('correspondences', function (Blueprint $table) {
            $table->string('sender')->nullable(false)->change();
            $table->string('recipient')->nullable(false)->change();
        });
    }
};
