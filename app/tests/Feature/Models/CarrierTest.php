<?php

namespace Tests\Feature\Models;

use App\Models\Carrier;
use App\Models\PackageType;
use App\Models\Region;
use App\Models\ShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrierTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_be_created_with_a_name(): void
    {
        $carrier = Carrier::create(['name' => 'PostNL']);

        $this->assertDatabaseHas('carriers', [
            'id' => $carrier->id,
            'name' => 'PostNL',
        ]);
    }

    public function test_it_has_many_shipping_rates(): void
    {
        $carrier = Carrier::create(['name' => 'PostNL']);
        $packageType = PackageType::create(['name' => 'Parcel']);
        $region = Region::create(['name' => 'Netherlands', 'iso' => 'NL']);

        $rate = ShippingRate::create([
            'carrier_id' => $carrier->id,
            'package_type_id' => $packageType->id,
            'region_id' => $region->id,
            'weekend' => false,
            'price' => 5.95,
        ]);

        $this->assertTrue($carrier->shippingRates->contains($rate));
    }
}
