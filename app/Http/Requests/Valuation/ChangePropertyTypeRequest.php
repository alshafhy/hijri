<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use Illuminate\Foundation\Http\FormRequest;

class ChangePropertyTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('changePropertyType', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'property_kind' => ['required', 'string', 'max:45'],
            'property_type' => ['required', 'string', 'max:32'],
        ];
    }
}
