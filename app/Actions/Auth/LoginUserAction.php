<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginUserAction
{
    /**
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function __invoke(string $email, string $password): array
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => [__('auth.invalid_credentials')],
            ]);
        }

        /** @var User $user */
        $user = User::query()->where('email', $email)->firstOrFail();

        $token = $user->createToken((string) config('users.api_token_name'))->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
