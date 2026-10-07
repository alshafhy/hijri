<?php

declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Models\Offer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Offer::class) ?? false;
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
