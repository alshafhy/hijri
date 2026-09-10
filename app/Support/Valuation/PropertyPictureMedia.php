<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Models\PropertyPicture;
use App\Support\Legacy\PropertyPictureVerifier;
use Illuminate\Support\Collection;

/**
 * Safe presentation / PDF helpers for property pictures when files may be missing.
 */
final class PropertyPictureMedia
{
    public function __construct(
        private readonly PropertyPictureVerifier $verifier = new PropertyPictureVerifier,
    ) {}

    public function isAvailable(PropertyPicture $picture): bool
    {
        if (! $picture->file_exists) {
            return false;
        }

        $resolved = $this->verifier->resolve($picture);

        return $resolved['found'] === true;
    }

    public function absolutePath(PropertyPicture $picture): ?string
    {
        if (! $picture->file_exists) {
            return null;
        }

        return $this->verifier->resolve($picture)['absolute_path'];
    }

    /**
     * Data-URI SVG placeholder — never a broken &lt;img&gt; src.
     */
    public function placeholderDataUri(): string
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="320" height="240" viewBox="0 0 320 240" role="img" aria-label="Image unavailable">
  <rect width="320" height="240" fill="#e9ecef"/>
  <rect x="24" y="24" width="272" height="192" fill="none" stroke="#adb5bd" stroke-width="2" stroke-dasharray="8 6"/>
  <text x="160" y="120" text-anchor="middle" fill="#6c757d" font-family="sans-serif" font-size="14">Image unavailable</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * @return array{src: string, available: bool, label: string}
     */
    public function display(PropertyPicture $picture): array
    {
        $label = trim((string) ($picture->description ?: $picture->filename)) ?: 'picture';

        if ($this->isAvailable($picture)) {
            return [
                'src' => route('dashboard.property-pictures.file', $picture),
                'available' => true,
                'label' => $label,
            ];
        }

        return [
            'src' => $this->placeholderDataUri(),
            'available' => false,
            'label' => $label,
        ];
    }

    /**
     * Build PDF-safe image list. Never throws; missing files become notes.
     *
     * @param  Collection<int, PropertyPicture>  $pictures
     * @return array{images: list<array{path: string, label: string}>, missing: list<string>, notes: list<string>}
     */
    public function forPdfReport(Collection $pictures): array
    {
        $images = [];
        $missing = [];
        $notes = [];

        foreach ($pictures as $picture) {
            $label = trim((string) ($picture->description ?: $picture->filename)) ?: 'picture #'.$picture->id;
            $path = $this->absolutePath($picture);

            if ($path !== null) {
                $images[] = ['path' => $path, 'label' => $label];

                continue;
            }

            $missing[] = $label;
            $notes[] = __('Image unavailable').': '.$label;
        }

        return [
            'images' => $images,
            'missing' => $missing,
            'notes' => $notes,
        ];
    }
}
