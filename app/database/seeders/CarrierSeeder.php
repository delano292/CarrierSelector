<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['PostNL', 'DHL', 'DPD', 'UPS'] as $name) {
            Carrier::firstOrCreate(['name' => $name]);
        }
    }
}
