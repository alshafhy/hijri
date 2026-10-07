<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use App\Http\Requests\Valuation\Concerns\ValidatesInitialData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCoordinatorValuationRequest extends FormRequest
{
    use ValidatesInitialData;

    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('editInitialData', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge($this->initialDataRules(), [
            'deposit_number' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateNeighborhoodBelongsToCity($validator);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'x_axis.required' => __('The property location was not selected on the map.'),
            'y_axis.required' => __('The property location was not selected on the map.'),
        ];
    }
}
