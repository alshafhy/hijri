<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\ValuationAttachment;
use App\Models\ValuationRequest;
use App\Support\Pdf\ReportImage;

/**
 * Image attachments embedded in the report appendix (the official Qima report is excluded).
 */
final class AttachmentImagesSection
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * @return list<array{src: string, label: string}>
     */
    public function build(ValuationRequest $request): array
    {
        $images = [];
        foreach ($request->attachments()->orderBy('id')->get() as $attachment) {
            if (! $this->isImage($attachment) || $this->isOfficialReport($attachment)) {
                continue;
            }

            $src = $this->source($attachment);
            if ($src !== null) {
                $images[] = ['src' => $src, 'label' => $this->label($attachment)];
            }
        }

        return $images;
    }

    private function isImage(ValuationAttachment $attachment): bool
    {
        $mime = trim((string) $attachment->mime_type);
        if ($mime !== '') {
            return str_starts_with($mime, 'image/');
        }

        $extension = strtolower(pathinfo($attachment->original_name ?: (string) $attachment->path, PATHINFO_EXTENSION));

        return in_array($extension, self::IMAGE_EXTENSIONS, true);
    }

    private function isOfficialReport(ValuationAttachment $attachment): bool
    {
        return trim((string) $attachment->type) === (string) config('valuation_form.official_report_type');
    }

    private function source(ValuationAttachment $attachment): ?string
    {
        $path = $attachment->absolutePath();

        return $attachment->mime_type === 'image/webp' ? $this->webpAsPng($path) : ReportImage::fromPath($path);
    }

    /**
     * mPDF does not render WebP reliably, so it is re-encoded as PNG when GD supports it.
     */
    private function webpAsPng(?string $path): ?string
    {
        if ($path === null || ! is_file($path) || ! function_exists('imagecreatefromwebp')) {
            return null;
        }

        $image = @imagecreatefromwebp($path);
        if ($image === false) {
            return null;
        }

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();

        return $png === '' ? null : 'data:image/png;base64,'.base64_encode($png);
    }

    private function label(ValuationAttachment $attachment): string
    {
        $type = trim((string) $attachment->type);

        return $type !== '' ? $type : $attachment->displayName();
    }
}
