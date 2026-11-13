<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class UpdateOwnProfileAction
{
    public const IMAGE_TYPES = ['signature' => 'signature_path', 'avatar' => 'avatar_path'];

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? null,
            'membership_number' => $data['membership_number'] ?? null,
            'membership_category' => $data['membership_category'] ?? null,
            'membership_branch_type' => $data['membership_branch_type'] ?? null,
        ])->save();

        return $user;
    }

    public function uploadImage(User $user, string $type, UploadedFile $file): User
    {
        $column = self::IMAGE_TYPES[$type];
        $previous = $user->{$column};

        $path = $file->storeAs("users/{$user->id}", $type.'-'.now()->format('YmdHis').'.'.$file->extension(), 'local');

        $user->forceFill([$column => $path])->save();

        if ($previous && $previous !== $path) {
            Storage::disk('local')->delete($previous);
        }

        return $user;
    }
}
