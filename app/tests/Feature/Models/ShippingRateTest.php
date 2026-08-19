<?php

namespace Tests\Feature\Models;

use App\Models\Carrier;
use App\Models\PackageType;
use App\Models\Region;
use App\Models\ShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingRateTest extends TestCase
{
    use RefreshDatabase;

    private function makeShippingRate(array $overrides = []): ShippingRate
    {
        $carrier = Carrier::create(['name' => 'PostNL']);
        $packageType = PackageType::create(['name' => 'Parcel']);
        $region = Region::create(['name' => 'Netherlands', 'iso' => 'NL']);

        return ShippingRate::create(array_merge([
            'carrier_id' => $carrier->id,
            'package_type_id' => $packageType->id,
            'region_id' => $region->id,
            'weekend' => false,
            'price' => 5.95,
        ], $overrides));
    }

    public function test_it_can_be_created_with_its_attributes(): void
    {
        $rate = $this->makeShippingRate();

        $this->assertDatabaseHas('shipping_rates', [
            'id' => $rate->id,
            'carrier_id' => $rate->carrier_id,
            'package_type_id' => $rate->package_type_id,
            'region_id' => $rate->region_id,
            'weekend' => false,
            'price' => 5.95,
        ]);
    }

    public function test_weekend_is_cast_to_boolean(): void
    {
        $rate = $this->makeShippingRate(['weekend' => 1]);

        $this->assertTrue($rate->weekend);
        $this->assertIsBool($rate->weekend);
    }

    public function test_price_is_cast_to_a_two_decimal_value(): void
    {
        $rate = $this->makeShippingRate(['price' => 5.9]);

        $this->assertSame('5.90', $rate->price);
    }

    public function test_it_belongs_to_a_carrier(): void
    {
        $rate = $this->makeShippingRate();

        $this->assertInstanceOf(Carrier::class, $rate->carrier);
    }

    public function test_it_belongs_to_a_package_type(): void
    {
        $rate = $this->makeShippingRate();

        $this->assertInstanceOf(PackageType::class, $rate->packageType);
    }

    public function test_it_belongs_to_a_region(): void
    {
        $rate = $this->makeShippingRate();

        $this->assertInstanceOf(Region::class, $rate->region);
    }

    public function test_it_is_deleted_when_its_carrier_is_deleted(): void
    {
        $rate = $this->makeShippingRate();
        $carrierId = $rate->carrier_id;

        Carrier::find($carrierId)->delete();

        $this->assertDatabaseMissing('shipping_rates', ['id' => $rate->id]);
    }
}
