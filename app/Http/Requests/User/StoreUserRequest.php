<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')],
        ];
    }
}
