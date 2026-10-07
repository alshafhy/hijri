<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use App\Models\ValuationRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreValuationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ValuationRequest::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'max:250'],
            'deposit_number' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'coordinator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'evaluator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'customer_name' => ['nullable', 'string', 'max:128'],
            'owner_name' => ['nullable', 'string', 'max:128'],
            'property_kind' => ['nullable', 'string', 'max:45'],
            'property_type' => ['nullable', 'string', 'max:32'],
            'instrument_no' => ['nullable', 'string', 'max:200'],
            'instrument_date' => ['nullable', 'string', 'max:64'],
            'instrument_gregorian_date' => ['nullable', 'string', 'max:64'],
            'city_id' => ['nullable', 'integer', Rule::exists('geo_cities', 'id')],
            'neighborhood_id' => ['nullable', 'integer', Rule::exists('geo_neighborhoods', 'id')],
            'street' => ['nullable', 'string', 'max:45'],
            'sketch_no' => ['nullable', 'string', 'max:45'],
            'part_no' => ['nullable', 'string', 'max:45'],
            'piece_number' => ['nullable', 'string', 'max:100'],
            'x_axis' => ['nullable', 'string', 'max:45'],
            'y_axis' => ['nullable', 'string', 'max:45'],
            'coordinator_share' => ['nullable', 'integer', 'min:0', 'max:100'],
            'evaluator_share' => ['nullable', 'integer', 'min:0', 'max:100'],
            'manager_share' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
