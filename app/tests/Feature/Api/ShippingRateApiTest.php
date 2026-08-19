<?php

namespace Tests\Feature\Api;

use App\Models\Carrier;
use App\Models\PackageType;
use App\Models\Region;
use App\Models\ShippingRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingRateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_returns_all_matching_carriers_on_a_weekday(): void
    {
        // 2026-08-24 is a Monday.
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Mailbox',
        ]));

        $response->assertOk();
        $response->assertJsonCount(4, 'data');
        $response->assertJsonFragment(['carrier' => 'PostNL', 'price' => '3.95']);
        $response->assertJsonFragment(['carrier' => 'DHL', 'price' => '4.45']);
        $response->assertJsonFragment(['carrier' => 'DPD', 'price' => '4.75']);
        $response->assertJsonFragment(['carrier' => 'UPS', 'price' => '4.25']);

        // Cheapest first: PostNL 3.95, UPS 4.25, DHL 4.45, DPD 4.75.
        $response->assertJsonPath('data.0.carrier', 'PostNL');
        $response->assertJsonPath('data.1.carrier', 'UPS');
        $response->assertJsonPath('data.2.carrier', 'DHL');
        $response->assertJsonPath('data.3.carrier', 'DPD');
    }

    public function test_it_excludes_carriers_without_weekend_delivery_on_a_weekend(): void
    {
        // 2026-08-22 is a Saturday.
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '2026-08-22',
            'package_type' => 'Mailbox',
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['carrier' => 'PostNL', 'price' => '3.95']);
        $response->assertJsonFragment(['carrier' => 'DHL', 'price' => '4.45']);
        $response->assertJsonMissing(['carrier' => 'DPD']);
        $response->assertJsonMissing(['carrier' => 'UPS']);
    }

    public function test_it_maps_belgium_to_its_own_region(): void
    {
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'BE',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Standard',
        ]));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['carrier' => 'PostNL', 'price' => '7.95']);
        $response->assertJsonFragment(['carrier' => 'DHL', 'price' => '8.45']);
        $response->assertJsonFragment(['carrier' => 'DPD', 'price' => '8.75']);
    }

    public function test_it_maps_other_eu_countries_to_the_eu_region(): void
    {
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'DE',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Standard',
        ]));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['carrier' => 'PostNL', 'price' => '10.95']);
        $response->assertJsonFragment(['carrier' => 'DHL', 'price' => '10.45']);
        $response->assertJsonFragment(['carrier' => 'DPD', 'price' => '10.75']);

        // Cheapest first: DHL 10.45, DPD 10.75, PostNL 10.95.
        $response->assertJsonPath('data.0.carrier', 'DHL');
        $response->assertJsonPath('data.1.carrier', 'DPD');
        $response->assertJsonPath('data.2.carrier', 'PostNL');
    }

    public function test_it_maps_non_eu_countries_to_the_rest_of_world_region(): void
    {
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'US',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Standard',
        ]));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['carrier' => 'PostNL', 'price' => '13.95']);
        $response->assertJsonFragment(['carrier' => 'DHL', 'price' => '12.45']);
        $response->assertJsonFragment(['carrier' => 'DPD', 'price' => '12.75']);
    }

    public function test_it_returns_an_empty_list_when_nothing_matches(): void
    {
        // Rest of World has no weekend-capable carriers for Standard.
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'US',
            'shipment_date' => '2026-08-22',
            'package_type' => 'Standard',
        ]));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_it_requires_country_shipment_date_and_package_type(): void
    {
        $response = $this->getJson('/api/shipping-rates');

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country', 'shipment_date', 'package_type']);
    }

    public function test_it_rejects_a_country_that_is_not_a_two_letter_code(): void
    {
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'Netherlands',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Standard',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country']);
    }

    public function test_it_rejects_a_malformed_shipment_date(): void
    {
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '24-08-2026',
            'package_type' => 'Standard',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['shipment_date']);
    }

    public function test_it_rejects_a_package_type_that_does_not_exist(): void
    {
        $response = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Envelope',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['package_type']);
    }

    public function test_it_paginates_to_20_results_per_page_cheapest_first(): void
    {
        $region = Region::where('iso', 'NL')->firstOrFail();
        $packageType = PackageType::create(['name' => 'Envelope']);

        foreach (range(1, 25) as $i) {
            ShippingRate::create([
                'carrier_id' => Carrier::create(['name' => "Carrier {$i}"])->id,
                'package_type_id' => $packageType->id,
                'region_id' => $region->id,
                'weekend' => false,
                // Inserted in descending price order so ascending output proves real sorting.
                'price' => 30 - $i,
            ]);
        }

        $firstPage = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Envelope',
        ]));

        $firstPage->assertOk();
        $firstPage->assertJsonCount(20, 'data');
        $firstPage->assertJsonPath('meta.total', 25);
        $firstPage->assertJsonPath('meta.per_page', 20);
        $firstPage->assertJsonPath('meta.current_page', 1);
        $firstPage->assertJsonPath('data.0.price', '5.00');
        $firstPage->assertJsonPath('data.19.price', '24.00');

        $secondPage = $this->getJson('/api/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Envelope',
            'page' => 2,
        ]));

        $secondPage->assertOk();
        $secondPage->assertJsonCount(5, 'data');
        $secondPage->assertJsonPath('meta.current_page', 2);
        $secondPage->assertJsonPath('data.0.price', '25.00');
        $secondPage->assertJsonPath('data.4.price', '29.00');
    }
}
