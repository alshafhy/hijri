<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\DTOs\User\CreateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action(CreateUserData::fromRequest($request));

        return response()->json([
            'message' => __('auth.registered'),
            'user' => new UserResource($result['user']->loadMissing('roles')),
            'access_token' => $result['token'],
            'token_type' => (string) config('users.token_type'),
        ], 201);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action(
            (string) $request->input('email'),
            (string) $request->input('password'),
        );

        return response()->json([
            'message' => __('auth.login_successful'),
            'user' => new UserResource($result['user']->loadMissing('roles')),
            'access_token' => $result['token'],
            'token_type' => (string) config('users.token_type'),
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->loadMissing('roles')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => __('auth.logged_out'),
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => __('auth.logged_out_all'),
        ]);
    }
}
