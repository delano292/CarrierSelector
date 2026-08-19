<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetShippingRatesRequest;
use App\Http\Resources\ShippingRateResource;
use App\Services\ShippingRateService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShippingRateController extends Controller
{
    public function __construct(private readonly ShippingRateService $shippingRateService)
    {
    }

    public function index(GetShippingRatesRequest $request): AnonymousResourceCollection
    {
        $shippingRates = $this->shippingRateService->getShippingRates(
            $request->country(),
            $request->shipmentDate(),
            $request->packageType(),
        );

        return ShippingRateResource::collection($shippingRates);
    }
}
