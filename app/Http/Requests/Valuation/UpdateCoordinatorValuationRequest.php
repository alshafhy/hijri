<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoordinatorValuationRequest extends FormRequest
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
            'number' => ['nullable', 'string', 'max:250'],
            'deposit_number' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'evaluator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'customer_name' => ['nullable', 'string', 'max:128'],
            'owner_name' => ['nullable', 'string', 'max:128'],
            'instrument_no' => ['nullable', 'string', 'max:200'],
            'property_kind' => ['nullable', 'string', 'max:45'],
            'property_type' => ['nullable', 'string', 'max:32'],
        ];
    }
}
