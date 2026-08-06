<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->json('sites')->nullable()->after('site');
        });

        DB::table('contract_documents')
            ->whereNotNull('site')
            ->orderBy('id')
            ->cursor()
            ->each(fn (object $row) => DB::table('contract_documents')
                ->where('id', $row->id)
                ->update(['sites' => json_encode([$row->site])]));

        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropIndex(['site']);
            $table->dropColumn('site');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->string('site')->nullable()->index()->after('sites');
        });

        DB::table('contract_documents')
            ->whereNotNull('sites')
            ->orderBy('id')
            ->cursor()
            ->each(function (object $row): void {
                $sites = json_decode($row->sites, true);

                DB::table('contract_documents')->where('id', $row->id)->update([
                    'site' => $sites[0] ?? null,
                ]);
            });

        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn('sites');
        });
    }
};
