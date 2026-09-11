<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluatorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('update', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:128'],
            'owner_name' => ['nullable', 'string', 'max:128'],
            'customer_name_en' => ['nullable', 'string', 'max:250'],
            'owner_name_en' => ['nullable', 'string', 'max:200'],
            'property_kind' => ['nullable', 'string', 'max:45'],
            'property_type' => ['nullable', 'string', 'max:32'],
            'instrument_no' => ['nullable', 'string', 'max:200'],
            'instrument_date' => ['nullable', 'string', 'max:64'],
            'instrument_gregorian_date' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
            'notes_property' => ['nullable', 'string'],
            'notes_real_estate' => ['nullable', 'string'],
            'valuation_way_type' => ['nullable', 'string'],
            'base_value' => ['nullable', 'string', 'max:100'],
            'forced_sale_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'evaluation_date' => ['nullable', 'date'],
            'location' => ['nullable', 'array'],
            'location.city_id' => ['nullable', 'integer', Rule::exists('geo_cities', 'id')],
            'location.neighborhood_id' => ['nullable', 'integer', Rule::exists('geo_neighborhoods', 'id')],
            'location.street' => ['nullable', 'string', 'max:45'],
            'location.sketch_no' => ['nullable', 'string', 'max:45'],
            'location.part_no' => ['nullable', 'string', 'max:45'],
            'location.piece_number' => ['nullable', 'string', 'max:100'],
            'location.x_axis' => ['nullable', 'string', 'max:45'],
            'location.y_axis' => ['nullable', 'string', 'max:45'],
            'location.sketch_name' => ['nullable', 'string', 'max:45'],
            'components' => ['nullable', 'array'],
            'components.*.area_value' => ['nullable', 'numeric'],
            'components.*.price_value' => ['nullable', 'numeric'],
        ];
    }
}
