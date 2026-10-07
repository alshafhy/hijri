<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Stores valuation uploads on the local disk and remembers them so a failed transaction can remove them.
 */
final class ValuationUploadStore
{
    /** @var list<string> */
    private array $stored = [];

    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');
        if ($path === false) {
            throw new RuntimeException('Unable to store uploaded file.');
        }
        $this->stored[] = $path;

        return $path;
    }

    /**
     * @param  array<int, array{file?: mixed, type?: mixed}>  $attachments
     */
    public function storeAttachments(User $actor, ValuationRequest $request, array $attachments): void
    {
        foreach ($attachments as $item) {
            $file = $item['file'] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $path = $this->store($file, 'valuation-attachments/'.$request->id);
            ValuationAttachment::query()->create([
                'valuation_request_id' => $request->id,
                'type' => $item['type'] ?? null,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $actor->id,
            ]);
        }
    }

    public function rollback(): void
    {
        foreach ($this->stored as $path) {
            Storage::disk('local')->delete($path);
        }
        $this->stored = [];
    }
}
