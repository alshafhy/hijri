<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeSharesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('manageFeeShares', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'coordinator_share' => ['required', 'integer', 'min:0', 'max:100'],
            'evaluator_share' => ['required', 'integer', 'min:0', 'max:100'],
            'manager_share' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
