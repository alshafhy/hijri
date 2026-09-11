<?php

declare(strict_types=1);

namespace App\Http\Requests\Contractor;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $owner = $this->route('partner') ?? $this->route('contractor');

        return $owner !== null && ($this->user()?->can('update', $owner) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:32'],
        ];
    }
}
