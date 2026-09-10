<?php

declare(strict_types=1);

namespace App\DTOs\User;

use Illuminate\Foundation\Http\FormRequest;

final readonly class ChangePasswordData
{
    public function __construct(
        public string $oldPassword,
        public string $newPassword,
    ) {
    }

    public static function fromRequest(FormRequest $request): self
    {
        return new self(
            oldPassword: (string) $request->input('old_password'),
            newPassword: (string) $request->input('new_password'),
        );
    }
}
