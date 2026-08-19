<?php

namespace App\Services;

use App\Models\ShippingRate;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShippingRateService
{
    private const PER_PAGE = 20;

    /**
     * ISO 3166-1 alpha-2 codes of EU member states, excluding NL and BE
     * which have their own dedicated region.
     *
     * @var array<int, string>
     */
    private const EU_COUNTRY_CODES = [
        'AT', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR',
        'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'PL', 'PT', 'RO', 'SK',
        'SI', 'ES', 'SE',
    ];

    public function getShippingRates(?string $country, ?CarbonInterface $shipmentDate, ?string $packageType): LengthAwarePaginator
    {
        $query = ShippingRate::query()
            ->with(['carrier', 'region', 'packageType'])
            // Excludes rates left behind by a soft-deleted carrier/region/package type.
            ->whereHas('carrier')
            ->orderBy('price');

        $query->whereHas('region', function ($query) use ($country) {
            if ($country !== null) {
                $query->where('iso', $this->resolveRegionIso($country));
            }
        });

        $query->whereHas('packageType', function ($query) use ($packageType) {
            if ($packageType !== null) {
                $query->where('name', $packageType);
            }
        });

        // Only carriers flagged for weekend delivery can serve a shipment on a Saturday or Sunday.
        if ($shipmentDate !== null && $shipmentDate->isWeekend()) {
            $query->where('weekend', true);
        }

        return $query->paginate(self::PER_PAGE);
    }

    private function resolveRegionIso(string $country): string
    {
        return match (true) {
            in_array($country, ['NL', 'BE'], true) => $country,
            in_array($country, self::EU_COUNTRY_CODES, true) => 'EU',
            default => 'ROW',
        };
    }
}
