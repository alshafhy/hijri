<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use Illuminate\Foundation\Http\FormRequest;

class ToggleQimaStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('toggleQimaStatus', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'uploaded_on_qima' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('uploaded_on_qima')) {
            $this->merge([
                'uploaded_on_qima' => filter_var($this->input('uploaded_on_qima'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }
}
