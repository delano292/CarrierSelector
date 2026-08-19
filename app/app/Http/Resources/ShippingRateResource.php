<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingRateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'carrier' => $this->carrier->name,
            'region' => $this->region->name,
            'package_type' => $this->packageType->name,
            'weekend' => $this->weekend,
            'price' => (string) $this->price,
        ];
    }
}
