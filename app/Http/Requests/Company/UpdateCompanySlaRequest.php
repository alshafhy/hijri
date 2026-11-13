<?php

declare(strict_types=1);

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySlaRequest extends FormRequest
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
            'request_sla_hours' => ['required', 'integer', 'min:1', 'max:8760'],
            'evaluation_sla_hours' => ['required', 'integer', 'min:1', 'max:8760'],
        ];
    }
}
