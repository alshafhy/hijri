<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupportRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $supportRequests = SupportRequest::query()
            ->with('user:id,name,email')
            ->when(in_array($status, [SupportRequest::STATUS_OPEN, SupportRequest::STATUS_CLOSED], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('support_requests.index', compact('supportRequests', 'status'));
    }

    public function update(Request $request, SupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([SupportRequest::STATUS_OPEN, SupportRequest::STATUS_CLOSED])],
        ]);

        $supportRequest->update($validated);

        return back()->with('success', __('Support request updated.'));
    }
}
