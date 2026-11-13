<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ValuationAttachment extends Model
{
    protected $fillable = [
        'legacy_key',
        'valuation_request_id',
        'type',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valuation_request_id' => 'integer',
            'size' => 'integer',
            'uploaded_by' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ValuationRequest, $this>
     */
    public function valuationRequest(): BelongsTo
    {
        return $this->belongsTo(ValuationRequest::class);
    }

    public function absolutePath(): ?string
    {
        if ($this->disk === 'legacy') {
            foreach ((array) config('legacy_import.attachment_search_paths', []) as $base) {
                $candidate = rtrim((string) $base, '/').'/'.ltrim($this->path, '/');
                if (is_file($candidate)) {
                    return $candidate;
                }
            }

            return null;
        }

        $storage = Storage::disk($this->disk ?: 'local');

        return $storage->exists($this->path) ? $storage->path($this->path) : null;
    }

    public function displayName(): string
    {
        return $this->original_name ?: basename($this->path);
    }
}
