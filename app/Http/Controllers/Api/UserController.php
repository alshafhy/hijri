<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserAction;
use App\DTOs\User\CreateUserData;
use App\DTOs\User\UpdateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when(
                $request->filled('q'),
                fn ($query) => $query->search($request->string('q')->toString())
            )
            ->paginate();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $user = $action(CreateUserData::fromRequest($request));

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action
    ): UserResource {
        $user = $action($user, UpdateUserData::fromRequest($request));

        return new UserResource($user);
    }

    public function destroy(User $user, DeleteUserAction $action): JsonResponse
    {
        $this->authorize('delete', $user);

        $action($user);

        return response()->json([
            'message' => __('messages.deleted', ['model' => __('models/users.singular')]),
        ]);
    }
}
