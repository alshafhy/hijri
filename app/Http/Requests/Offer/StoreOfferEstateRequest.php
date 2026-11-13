<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Support\Valuation\ValuationFieldGrid;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferEstateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $offer = $this->route('offer');

        return $this->user()?->can('update', $offer) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $kinds = ValuationFieldGrid::kinds();
        $kind = (string) $this->input('estate_kind');

        return [
            'estate_kind' => ['required', 'string', Rule::in(array_keys($kinds))],
            'estate_type' => ['required', 'string', Rule::in($kinds[$kind] ?? [])],
            'instrument_no' => ['nullable', 'string', 'max:64'],
            'area' => ['nullable', 'integer', 'min:0'],
            'neighborhood' => ['nullable', 'string', 'max:64', Rule::exists('geo_neighborhoods', 'name_ar')],
            'fees' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'estate_kind' => __('Property kind'),
            'estate_type' => __('Property type'),
            'instrument_no' => __('Instrument no'),
            'area' => __('Area'),
            'neighborhood' => __('Neighborhood'),
            'fees' => __('Amount'),
        ];
    }
}
