<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfferRequest extends FormRequest
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
            'number' => ['required', 'string', 'max:100'],
            'partner_id' => ['nullable', 'integer', Rule::exists('partners', 'id')],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'offered_at' => ['nullable', 'date'],
            'valuation_request_id' => ['nullable', 'integer', Rule::exists('valuation_requests', 'id')],
        ];
    }
}
