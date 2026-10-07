<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientLogRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

final class ClientLogController extends Controller
{
    public function store(StoreClientLogRequest $request): Response
    {
        Log::channel('client')->warning('['.$request->validated('type').'] '.$request->validated('message'), array_merge(
            $request->safe()->except(['type', 'message']),
            [
                'user_id' => $request->user()?->getAuthIdentifier(),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 300),
            ],
        ));

        return response()->noContent();
    }
}
