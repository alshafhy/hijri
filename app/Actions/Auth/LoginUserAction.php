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
    public function __invoke(string $login, string $password): array
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $login, 'password' => $password])) {
            throw ValidationException::withMessages([
                $field => [__('auth.invalid_credentials')],
            ]);
        }

        /** @var User $user */
        $user = User::query()->where($field, $login)->firstOrFail();

        $token = $user->createToken((string) config('users.api_token_name'))->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
