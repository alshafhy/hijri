<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\ChangePasswordData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class ChangePasswordAction
{
    public function __invoke(User $user, ChangePasswordData $data): User
    {
        if (! Hash::check($data->oldPassword, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => [__('auth.old_password_mismatch')],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data->newPassword),
            'pass_need_to_be_changed' => 0,
        ])->save();

        return $user->fresh() ?? $user;
    }
}
