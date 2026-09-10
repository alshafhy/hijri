<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'branch_id' => $this->branch_id,
            'status' => $this->status,
            'roles' => $this->roles
                ->map(static fn ($role): array => [
                    'name' => $role->name,
                    'ar_name' => $role->ar_name,
                ])
                ->values()
                ->all(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
