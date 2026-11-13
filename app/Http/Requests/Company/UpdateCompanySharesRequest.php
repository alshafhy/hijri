<?php

declare(strict_types=1);

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySharesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $this->user()?->can('update', $company) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'default_coordinator_share' => ['required', 'integer', 'min:0', 'max:100'],
            'default_evaluator_share' => ['required', 'integer', 'min:0', 'max:100'],
            'default_manager_share' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
