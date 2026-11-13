<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation\Concerns;

use App\Models\GeoNeighborhood;
use App\Support\Valuation\ValuationFieldGrid;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Initial (coordinator) data shared by the create wizard and edit-initial-data (SYSTEM_SPEC §7.4–7.5).
 */
trait ValidatesInitialData
{
    /** @return array<string, mixed> */
    protected function initialDataRules(): array
    {
        $upload = config('valuation_form.upload');
        $short = ['nullable', 'string', 'max:45'];

        return [
            'number' => ['nullable', 'string', 'max:250'],
            'customer_name' => ['required', 'string', 'max:128'],
            'owner_name' => ['required', 'string', 'max:128'],
            'instrument_no' => ['required', 'string', 'max:200'],
            'instrument_date' => ['nullable', 'string', 'max:64', 'prohibits:instrument_gregorian_date'],
            'instrument_gregorian_date' => ['nullable', 'date'],
            'according_instrument' => ['nullable', 'boolean'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'license_date' => ['nullable', 'date'],
            'issued_by' => ['nullable', 'string', 'max:255'],
            'commissioning_date' => ['nullable', 'date'],
            'retail_no' => ['nullable', 'integer', 'min:0'],
            'undergo_reasons' => ['nullable', 'string', 'max:255'],
            'city_id' => ['required', 'integer', Rule::exists('geo_cities', 'id')],
            'neighborhood_id' => ['required', 'integer', Rule::exists('geo_neighborhoods', 'id')],
            'street' => $short,
            'sketch_no' => $short,
            'part_no' => $short,
            'piece_number' => ['nullable', 'string', 'max:100'],
            'evaluator_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'notes_real_estate' => ['nullable', 'string', 'max:5000'],
            'borders' => ['nullable', 'array'],
            'x_axis' => ['required', 'numeric', 'between:-90,90', 'not_in:0'],
            'y_axis' => ['required', 'numeric', 'between:-180,180', 'not_in:0'],
            'new_attachments' => ['nullable', 'array', 'max:20'],
            'new_attachments.*.file' => ['nullable', 'file', 'mimes:'.$upload['attachment_mimes'], 'max:'.$upload['attachment_max_kb']],
            'new_attachments.*.type' => ['nullable', 'string', Rule::in(config('valuation_form.attachment_types'))],
        ] + $this->borderRules();
    }

    /** @return array<string, mixed> */
    protected function propertyTypeRules(): array
    {
        return [
            'property_kind' => ['required', 'string', Rule::in(array_keys(ValuationFieldGrid::kinds()))],
            'property_type' => ['required', 'string', 'max:32', function (string $attribute, mixed $value, \Closure $fail): void {
                $kind = (string) $this->input('property_kind');
                if (! in_array(trim((string) $value), ValuationFieldGrid::kinds()[$kind] ?? [], true)) {
                    $fail(__('The selected property type does not belong to the property kind.'));
                }
            }],
            'components' => ['nullable', 'array'],
            'components.*.area_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, mixed> */
    private function borderRules(): array
    {
        $rules = [];
        foreach (['north', 'south', 'east', 'west'] as $side) {
            $rules["borders.$side"] = ['nullable', 'string', 'max:255'];
            $rules["borders.{$side}_length"] = ['nullable', 'numeric', 'min:0'];
        }

        return $rules;
    }

    protected function validateNeighborhoodBelongsToCity(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['city_id', 'neighborhood_id'])) {
                return;
            }
            $matches = GeoNeighborhood::query()
                ->whereKey($this->input('neighborhood_id'))
                ->where('city_id', $this->input('city_id'))
                ->exists();
            if (! $matches) {
                $validator->errors()->add('neighborhood_id', __('The neighborhood does not belong to the selected city.'));
            }
        });
    }
}
