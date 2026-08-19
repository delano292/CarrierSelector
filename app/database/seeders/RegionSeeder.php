<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'EU' => 'Europe',
            'ROW' => 'Rest of World',
        ];

        foreach ($regions as $iso => $name) {
            Region::firstOrCreate(['iso' => $iso], ['name' => $name]);
        }
    }
}
