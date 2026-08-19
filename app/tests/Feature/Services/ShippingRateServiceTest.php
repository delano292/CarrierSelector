<?php

namespace Tests\Feature\Services;

use App\Models\Carrier;
use App\Models\PackageType;
use App\Models\Region;
use App\Models\ShippingRate;
use App\Services\ShippingRateService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingRateServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShippingRateService $service;

    private PackageType $packageType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ShippingRateService();
        $this->packageType = PackageType::create(['name' => 'Standard']);
    }

    private function rateFor(string $regionIso, bool $weekend, float $price, ?string $carrierName = null): ShippingRate
    {
        $carrier = Carrier::create(['name' => $carrierName ?? 'Carrier '.$regionIso.'-'.($weekend ? 'weekend' : 'weekday')]);
        $region = Region::firstOrCreate(['iso' => $regionIso], ['name' => $regionIso]);

        return ShippingRate::create([
            'carrier_id' => $carrier->id,
            'package_type_id' => $this->packageType->id,
            'region_id' => $region->id,
            'weekend' => $weekend,
            'price' => $price,
        ]);
    }

    public function test_it_resolves_netherlands_to_its_own_region(): void
    {
        $rate = $this->rateFor('NL', false, 5.00);
        $this->rateFor('EU', false, 9.00);

        $results = $this->service->getShippingRates('NL', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertTrue($results->contains($rate));
        $this->assertCount(1, $results);
    }

    public function test_it_resolves_belgium_to_its_own_region(): void
    {
        $rate = $this->rateFor('BE', false, 5.00);
        $this->rateFor('EU', false, 9.00);

        $results = $this->service->getShippingRates('BE', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertTrue($results->contains($rate));
        $this->assertCount(1, $results);
    }

    public function test_it_resolves_other_eu_countries_to_the_eu_region(): void
    {
        $rate = $this->rateFor('EU', false, 9.00);

        foreach (['FR', 'DE', 'ES', 'IT'] as $country) {
            $results = $this->service->getShippingRates($country, CarbonImmutable::parse('2026-08-24'), 'Standard');

            $this->assertTrue($results->contains($rate), "Expected {$country} to resolve to the EU region.");
        }
    }

    public function test_it_resolves_unrecognised_countries_to_rest_of_world(): void
    {
        $rate = $this->rateFor('ROW', false, 12.00);

        $results = $this->service->getShippingRates('US', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertTrue($results->contains($rate));
        $this->assertCount(1, $results);
    }

    public function test_it_only_filters_by_package_type_within_the_resolved_region(): void
    {
        $this->rateFor('NL', false, 5.00);
        $otherPackageType = PackageType::create(['name' => 'Mailbox']);
        ShippingRate::create([
            'carrier_id' => Carrier::create(['name' => 'Other'])->id,
            'package_type_id' => $otherPackageType->id,
            'region_id' => Region::where('iso', 'NL')->first()->id,
            'weekend' => false,
            'price' => 2.00,
        ]);

        $results = $this->service->getShippingRates('NL', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertCount(1, $results);
    }

    public function test_it_includes_both_weekend_and_weekday_carriers_on_a_weekday(): void
    {
        $weekdayOnly = $this->rateFor('NL', false, 5.00, 'WeekdayOnly');
        $weekendCapable = $this->rateFor('NL', true, 6.00, 'WeekendCapable');

        // 2026-08-24 is a Monday.
        $results = $this->service->getShippingRates('NL', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertTrue($results->contains($weekdayOnly));
        $this->assertTrue($results->contains($weekendCapable));
        $this->assertCount(2, $results);
    }

    public function test_it_excludes_weekday_only_carriers_on_a_weekend(): void
    {
        $weekdayOnly = $this->rateFor('NL', false, 5.00, 'WeekdayOnly');
        $weekendCapable = $this->rateFor('NL', true, 6.00, 'WeekendCapable');

        // 2026-08-22 is a Saturday.
        $results = $this->service->getShippingRates('NL', CarbonImmutable::parse('2026-08-22'), 'Standard');

        $this->assertFalse($results->contains($weekdayOnly));
        $this->assertTrue($results->contains($weekendCapable));
        $this->assertCount(1, $results);
    }

    public function test_it_orders_results_cheapest_first(): void
    {
        $expensive = $this->rateFor('NL', false, 9.00, 'Expensive');
        $cheapest = $this->rateFor('NL', false, 1.00, 'Cheapest');
        $middle = $this->rateFor('NL', false, 5.00, 'Middle');

        $results = $this->service->getShippingRates('NL', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertSame(
            [$cheapest->id, $middle->id, $expensive->id],
            $results->pluck('id')->all(),
        );
    }

    public function test_it_paginates_to_20_results_per_page(): void
    {
        foreach (range(1, 25) as $i) {
            $this->rateFor('NL', false, (float) $i, "Carrier {$i}");
        }

        $firstPage = $this->service->getShippingRates('NL', CarbonImmutable::parse('2026-08-24'), 'Standard');

        $this->assertSame(20, $firstPage->perPage());
        $this->assertSame(25, $firstPage->total());
        $this->assertCount(20, $firstPage);
        $this->assertSame(1.0, (float) $firstPage->first()->price);
    }

    public function test_it_returns_every_rate_when_no_filters_are_given(): void
    {
        $nl = $this->rateFor('NL', false, 5.00);
        $eu = $this->rateFor('EU', false, 9.00);

        $results = $this->service->getShippingRates(null, null, null);

        $this->assertTrue($results->contains($nl));
        $this->assertTrue($results->contains($eu));
        $this->assertCount(2, $results);
    }

    public function test_it_filters_by_country_when_shipment_date_and_package_type_are_omitted(): void
    {
        $nl = $this->rateFor('NL', false, 5.00);
        $eu = $this->rateFor('EU', false, 9.00);

        $results = $this->service->getShippingRates('NL', null, null);

        $this->assertTrue($results->contains($nl));
        $this->assertFalse($results->contains($eu));
        $this->assertCount(1, $results);
    }

    public function test_it_does_not_apply_the_weekend_rule_when_shipment_date_is_omitted(): void
    {
        $weekdayOnly = $this->rateFor('NL', false, 5.00, 'WeekdayOnly');
        $weekendCapable = $this->rateFor('NL', true, 6.00, 'WeekendCapable');

        $results = $this->service->getShippingRates('NL', null, 'Standard');

        $this->assertTrue($results->contains($weekdayOnly));
        $this->assertTrue($results->contains($weekendCapable));
        $this->assertCount(2, $results);
    }

    public function test_it_excludes_rates_belonging_to_a_soft_deleted_carrier(): void
    {
        $rate = $this->rateFor('NL', false, 5.00, 'Discontinued');
        $rate->carrier->delete();

        $results = $this->service->getShippingRates('NL', null, 'Standard');

        $this->assertFalse($results->contains($rate));
        $this->assertCount(0, $results);
    }

    public function test_it_excludes_rates_belonging_to_a_soft_deleted_region(): void
    {
        $rate = $this->rateFor('NL', false, 5.00);
        $rate->region->delete();

        $results = $this->service->getShippingRates('NL', null, 'Standard');

        $this->assertFalse($results->contains($rate));
        $this->assertCount(0, $results);
    }

    public function test_it_excludes_rates_belonging_to_a_soft_deleted_package_type(): void
    {
        $rate = $this->rateFor('NL', false, 5.00);
        $this->packageType->delete();

        $results = $this->service->getShippingRates('NL', null, 'Standard');

        $this->assertFalse($results->contains($rate));
        $this->assertCount(0, $results);
    }
}
