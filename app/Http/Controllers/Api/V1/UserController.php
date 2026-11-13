<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\UpdateUserAction;
use App\DTOs\User\CreateUserData;
use App\DTOs\User\UpdateUserData;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with(['roles', 'branch'])
            ->when(
                $request->filled('q'),
                fn ($query) => $query->search($request->string('q')->toString())
            )
            ->paginate((int) $request->integer('per_page', 15));

        return ApiResponse::collection(
            UserResource::collection($users),
            ApiResponse::paginationMeta($users),
        );
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $user = $action(CreateUserData::fromRequest($request));

        return ApiResponse::resource(
            new UserResource($user->loadMissing(['roles', 'branch'])),
            ['message' => __('messages.saved', ['model' => __('models/users.singular')])],
            201,
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return ApiResponse::resource(
            new UserResource($user->loadMissing(['roles', 'branch'])),
        );
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action
    ): JsonResponse {
        $user = $action($user, UpdateUserData::fromRequest($request));

        return ApiResponse::resource(
            new UserResource($user->loadMissing(['roles', 'branch'])),
            ['message' => __('messages.updated', ['model' => __('models/users.singular')])],
        );
    }

    public function destroy(User $user, DeleteUserAction $action): JsonResponse
    {
        $this->authorize('delete', $user);

        $userId = $user->id;
        $action($user);

        RefreshUserMenuCacheJob::dispatch($userId);

        return ApiResponse::success(
            null,
            ['message' => __('messages.deleted', ['model' => __('models/users.singular')])],
        );
    }
}
