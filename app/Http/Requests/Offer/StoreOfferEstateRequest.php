<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'estate_kind' => ['nullable', 'string', 'max:64'],
            'estate_type' => ['required', 'string', 'max:255'],
            'instrument_no' => ['nullable', 'string', 'max:64'],
            'area' => ['nullable', 'integer', 'min:0'],
            'neighborhood' => ['nullable', 'string', 'max:64'],
            'fees' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
