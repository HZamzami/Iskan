<?php

namespace Database\Seeders;

use App\Models\GeoDocumentType;
use Illuminate\Database\Seeder;

class GeoDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['slug' => 'gis', 'name' => 'GIS', 'color' => 'info', 'site_scope' => 'all', 'accepted_extensions' => ['gpkg', 'rar', 'zip'], 'max_file_size' => 51200, 'sort_order' => 1],
            ['slug' => 'kml_kmz', 'name' => 'KML & KMZ', 'color' => 'success', 'site_scope' => 'all', 'accepted_extensions' => ['kml', 'kmz'], 'max_file_size' => 51200, 'sort_order' => 2],
            ['slug' => 'as_built_drawing', 'name' => 'المخططات كما نُفذت (As Built Drawing)', 'color' => 'warning', 'site_scope' => 'all', 'accepted_extensions' => ['pdf', 'dwg'], 'max_file_size' => 51200, 'sort_order' => 3],
        ];

        foreach ($rows as $row) {
            GeoDocumentType::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
