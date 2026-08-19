<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\PackageType;
use App\Models\Region;
use App\Models\ShippingRate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carrierIds = Carrier::pluck('id', 'name');
        $packageTypeIds = PackageType::pluck('id', 'name');
        $regionIds = Region::pluck('id', 'iso');

        $rates = [
            ['PostNL', 'Standard', 'NL', true, 6.95],
            ['PostNL', 'Mailbox', 'NL', true, 3.95],
            ['PostNL', 'Pallet', 'NL', false, 26.95],
            ['PostNL', 'Standard', 'BE', true, 7.95],
            ['PostNL', 'Mailbox', 'BE', true, 4.95],
            ['PostNL', 'Standard', 'EU', true, 10.95],
            ['PostNL', 'Standard', 'ROW', false, 13.95],
            ['DHL', 'Standard', 'NL', true, 7.45],
            ['DHL', 'Mailbox', 'NL', true, 4.45],
            ['DHL', 'Standard', 'BE', true, 8.45],
            ['DHL', 'Mailbox', 'BE', true, 5.45],
            ['DHL', 'Standard', 'EU', false, 10.45],
            ['DHL', 'Standard', 'ROW', false, 12.45],
            ['DPD', 'Standard', 'NL', true, 7.75],
            ['DPD', 'Mailbox', 'NL', false, 4.75],
            ['DPD', 'Pallet', 'NL', true, 21.75],
            ['DPD', 'Standard', 'BE', true, 8.75],
            ['DPD', 'Mailbox', 'BE', false, 5.75],
            ['DPD', 'Pallet', 'BE', true, 23.75],
            ['DPD', 'Standard', 'EU', false, 10.75],
            ['DPD', 'Standard', 'ROW', false, 12.75],
            ['UPS', 'Mailbox', 'NL', false, 4.25],
            ['UPS', 'Mailbox', 'BE', false, 5.25],
            ['UPS', 'Mailbox', 'EU', false, 6.25],
            ['UPS', 'Mailbox', 'ROW', false, 8.25],
        ];

        foreach ($rates as [$carrier, $packageType, $region, $weekend, $price]) {
            ShippingRate::firstOrCreate([
                'carrier_id' => $carrierIds[$carrier],
                'package_type_id' => $packageTypeIds[$packageType],
                'region_id' => $regionIds[$region],
            ], [
                'weekend' => $weekend,
                'price' => $price,
            ]);
        }
    }
}
