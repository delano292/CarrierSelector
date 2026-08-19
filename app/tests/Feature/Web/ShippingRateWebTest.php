<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingRateWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_returns_matching_carriers_from_the_web_route(): void
    {
        // 2026-08-24 is a Monday.
        $response = $this->getJson('/shipping-rates?'.http_build_query([
            'country' => 'NL',
            'shipment_date' => '2026-08-24',
            'package_type' => 'Standard',
        ]));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['carrier' => 'PostNL', 'price' => '6.95']);
    }

    public function test_it_allows_all_parameters_to_be_omitted(): void
    {
        $response = $this->getJson('/shipping-rates');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 25);
    }

    public function test_it_validates_the_same_way_as_the_api_route(): void
    {
        $response = $this->getJson('/shipping-rates?'.http_build_query(['country' => 'Netherlands']));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country']);
    }
}
