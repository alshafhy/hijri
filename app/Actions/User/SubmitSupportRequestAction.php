<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\SupportRequest;
use App\Models\User;

final class SubmitSupportRequestAction
{
    /**
     * @param  array{title: string, body: string}  $data
     */
    public function execute(User $user, array $data): SupportRequest
    {
        return SupportRequest::query()->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => SupportRequest::STATUS_OPEN,
        ]);
    }
}
