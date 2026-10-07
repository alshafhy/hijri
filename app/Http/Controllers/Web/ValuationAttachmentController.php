<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ValuationAttachment;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ValuationAttachmentController extends Controller
{
    public function download(ValuationRequest $valuationRequest, ValuationAttachment $attachment): BinaryFileResponse
    {
        abort_unless($attachment->valuation_request_id === $valuationRequest->id, 404);
        $this->authorize('view', $valuationRequest);

        $path = $attachment->absolutePath();
        abort_if($path === null, 404, __('File not found on disk.'));

        return response()->download($path, $attachment->displayName());
    }

    public function destroy(Request $request, ValuationRequest $valuationRequest, ValuationAttachment $attachment): RedirectResponse
    {
        abort_unless($attachment->valuation_request_id === $valuationRequest->id, 404);
        $this->authorize('deleteAttachment', $valuationRequest);

        if ($attachment->disk === 'local') {
            Storage::disk('local')->delete($attachment->path);
        }
        $attachment->delete();

        ValuationActivity::log($request->user(), $valuationRequest, 'attachment_deleted', 'Attachment deleted', [
            'attachment_id' => $attachment->id,
        ]);

        return back()->with('success', __('Attachment deleted.'));
    }
}
