<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

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
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles
                    ->map(static function (object $role): array {
                        return [
                            'name' => (string) data_get($role, 'name', ''),
                            'ar_name' => (string) data_get($role, 'ar_name', ''),
                        ];
                    })
                    ->values()
                    ->all();
            }, []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
