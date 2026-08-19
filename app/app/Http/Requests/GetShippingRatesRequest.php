<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GetShippingRatesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country' => ['nullable', 'string', 'regex:/^[A-Z]{2}$/'],
            'shipment_date' => ['nullable', 'date_format:Y-m-d'],
            'package_type' => ['nullable', 'string', 'exists:package_types,name'],
        ];
    }

    public function country(): ?string
    {
        return $this->validated('country');
    }

    public function shipmentDate(): ?CarbonImmutable
    {
        $shipmentDate = $this->validated('shipment_date');

        return $shipmentDate ? CarbonImmutable::createFromFormat('Y-m-d', $shipmentDate) : null;
    }

    public function packageType(): ?string
    {
        return $this->validated('package_type');
    }
}
