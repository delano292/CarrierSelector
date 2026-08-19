<?php

namespace Database\Seeders;

use App\Models\PackageType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Standard', 'Mailbox', 'Pallet'] as $name) {
            PackageType::firstOrCreate(['name' => $name]);
        }
    }
}
