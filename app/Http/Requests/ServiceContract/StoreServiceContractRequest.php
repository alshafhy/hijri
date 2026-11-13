<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceContract;

use App\Http\Requests\Valuation\Concerns\ValidatesInitialData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceContractRequest extends FormRequest
{
    use ValidatesInitialData;

    public function authorize(): bool
    {
        return $this->user()?->can('company.edit') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $propertyRules = $this->propertyTypeRules();

        return [
            'contract_date' => ['required', 'date'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_id_number' => ['required', 'string', 'max:50'],
            'client_phone' => ['required', 'string', 'max:30'],
            'client_address' => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'city_id' => ['required', 'integer', Rule::exists('geo_cities', 'id')],
            'neighborhood_id' => ['required', 'integer', Rule::exists('geo_neighborhoods', 'id')],
            'property_kind' => $propertyRules['property_kind'],
            'property_type' => $propertyRules['property_type'],
            'instrument_no' => ['required', 'string', 'max:100'],
            'instrument_date' => ['nullable', 'date'],
            'contract_value' => ['required', 'numeric', 'min:1', 'max:999999999'],
            'representative_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateNeighborhoodBelongsToCity($validator);
    }
}
