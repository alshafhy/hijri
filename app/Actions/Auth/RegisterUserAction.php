<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\User\CreateUserAction;
use App\DTOs\User\CreateUserData;
use App\Models\User;

final class RegisterUserAction
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
    ) {}

    /**
     * @return array{user: User, token: string}
     */
    public function __invoke(CreateUserData $data): array
    {
        $user = ($this->createUserAction)($data);

        $token = $user->createToken((string) config('users.api_token_name'))->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
