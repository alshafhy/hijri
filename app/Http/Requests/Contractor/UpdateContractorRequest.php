<?php

declare(strict_types=1);

namespace App\Http\Requests\Contractor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $contractor = $this->route('contractor');

        return $this->user()?->can('update', $contractor) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'x_axis' => ['nullable', 'numeric'],
            'y_axis' => ['nullable', 'numeric'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'fees' => ['nullable', 'integer', 'min:0'],
            'template_id' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
