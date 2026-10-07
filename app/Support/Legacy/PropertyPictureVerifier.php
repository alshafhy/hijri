<?php

declare(strict_types=1);

namespace App\Support\Legacy;

use App\Models\PropertyPicture;

/**
 * Resolves and verifies legacy picture files on disk.
 * Only touches file_exists + relative_path on the picture row.
 */
final class PropertyPictureVerifier
{
    /**
     * @return array{found: bool, absolute_path: ?string, relative_path: ?string, request_legacy_id: ?int}
     */
    public function resolve(PropertyPicture $picture, ?array $bases = null): array
    {
        $picture->loadMissing('property.valuationRequest');
        $requestLegacyId = $picture->property?->valuationRequest?->legacy_id;
        $filename = (string) $picture->filename;
        $bases ??= config('legacy_import.picture_search_paths', []);

        if (! is_array($bases) || $bases === [] || $filename === '') {
            return [
                'found' => false,
                'absolute_path' => null,
                'relative_path' => null,
                'request_legacy_id' => $requestLegacyId !== null ? (int) $requestLegacyId : null,
            ];
        }

        foreach ($bases as $base) {
            $base = rtrim((string) $base, '/');
            if ($base === '') {
                continue;
            }

            $candidates = [];
            if ($requestLegacyId) {
                // Canonical legacy layout: {base}/{idRequest}/{filename}
                $candidates[] = $base.'/'.$requestLegacyId.'/'.$filename;
            }
            $candidates[] = $base.'/'.$filename;

            foreach ($candidates as $absolute) {
                if (is_file($absolute)) {
                    return [
                        'found' => true,
                        'absolute_path' => $absolute,
                        'relative_path' => ltrim(str_replace($base, '', $absolute), '/'),
                        'request_legacy_id' => $requestLegacyId !== null ? (int) $requestLegacyId : null,
                    ];
                }
            }
        }

        return [
            'found' => false,
            'absolute_path' => null,
            'relative_path' => null,
            'request_legacy_id' => $requestLegacyId !== null ? (int) $requestLegacyId : null,
        ];
    }

    /**
     * Persist verification result. Only updates file_exists and relative_path.
     *
     * @return array{found: bool, absolute_path: ?string, relative_path: ?string, request_legacy_id: ?int}
     */
    public function verifyAndPersist(PropertyPicture $picture, ?array $bases = null): array
    {
        $result = $this->resolve($picture, $bases);

        $picture->forceFill([
            'file_exists' => $result['found'],
            'relative_path' => $result['relative_path'],
        ])->save();

        return $result;
    }
}
