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
            'country' => ['required', 'string', 'regex:/^[A-Z]{2}$/'],
            'shipment_date' => ['required', 'date_format:Y-m-d'],
            'package_type' => ['required', 'string', 'exists:package_types,name'],
        ];
    }

    public function country(): string
    {
        return $this->validated('country');
    }

    public function shipmentDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $this->validated('shipment_date'));
    }

    public function packageType(): string
    {
        return $this->validated('package_type');
    }
}
