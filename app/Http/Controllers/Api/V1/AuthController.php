<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\DTOs\User\CreateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action(CreateUserData::fromRequest($request));

        return ApiResponse::success([
            'user' => (new UserResource($result['user']->loadMissing('roles')))->resolve(),
            'access_token' => $result['token'],
            'token_type' => (string) config('users.token_type'),
        ], [
            'message' => __('auth.registered'),
        ], 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $login = (string) ($request->input('username') ?: $request->input('email'));

        $result = $action(
            $login,
            (string) $request->input('password'),
        );

        return ApiResponse::success([
            'user' => (new UserResource($result['user']->loadMissing('roles')))->resolve(),
            'access_token' => $result['token'],
            'token_type' => (string) config('users.token_type'),
        ], [
            'message' => __('auth.login_successful'),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return ApiResponse::resource(
            new UserResource($request->user()->loadMissing('roles')),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, [
            'message' => __('auth.logged_out'),
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, [
            'message' => __('auth.logged_out_all'),
        ]);
    }
}
