<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'membership_number' => ['nullable', 'string', 'max:100'],
            'membership_category' => ['nullable', 'string', 'max:100'],
            'membership_branch_type' => ['nullable', 'string', 'max:100'],
        ];
    }
}
