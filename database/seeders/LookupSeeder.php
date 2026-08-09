<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            RequirementGroupSeeder::class,
            MinuteTypeSeeder::class,
            GeoDocumentTypeSeeder::class,
            ContractDocumentTypeSeeder::class,
            FinancialFlowTypeSeeder::class,
            PeriodicReportTypeSeeder::class,
            ContractualRequirementTypeSeeder::class,
        ]);
    }
}
