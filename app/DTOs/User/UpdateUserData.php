<?php

declare(strict_types=1);

namespace App\DTOs\User;

use Illuminate\Foundation\Http\FormRequest;

final readonly class UpdateUserData
{
    /**
     * @param  array<int, int|string>  $roleIds
     */
    public function __construct(
        public string $name,
        public string $username,
        public string $email,
        public ?int $branchId,
        public array $roleIds = [],
    ) {}

    public static function fromRequest(FormRequest $request): self
    {
        $roleIds = $request->input('roles', $request->input('role_ids', []));

        return new self(
            name: (string) $request->input('name'),
            username: (string) $request->input('username'),
            email: (string) $request->input('email'),
            branchId: $request->filled('branch_id') ? (int) $request->input('branch_id') : null,
            roleIds: is_array($roleIds) ? array_values($roleIds) : [],
        );
    }
}
