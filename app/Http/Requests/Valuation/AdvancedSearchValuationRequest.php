<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use App\Models\ValuationRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvancedSearchValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('advancedSearch', ValuationRequest::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:64'],
            'uploaded_on_qima' => ['nullable'],
            'evaluator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'coordinator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'property_type' => ['nullable', 'string', 'max:32'],
            'property_kind' => ['nullable', 'string', 'max:45'],
            'city_id' => ['nullable', 'integer', Rule::exists('geo_cities', 'id')],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'approved_only' => ['nullable', 'boolean'],
        ];
    }
}
