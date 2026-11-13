<?php

declare(strict_types=1);

namespace App\Services\LegacyImport\Concerns;

use App\Models\LegacyUserMap;
use App\Models\Property;
use App\Models\ValuationRequest;

trait ResolvesImportedEntities
{
    /** @var array<int, int|null>|null */
    private static ?array $userMapCache = null;

    /** @var array<int, int>|null */
    private static ?array $propertyByLegacyCache = null;

    /** @var array<int, int>|null */
    private static ?array $requestByLegacyCache = null;

    protected function propertyIdByLegacyRei(int $legacyReiId): ?int
    {
        if (self::$propertyByLegacyCache === null) {
            self::$propertyByLegacyCache = Property::query()->pluck('id', 'legacy_id')->map(fn ($id) => (int) $id)->all();
        }

        return self::$propertyByLegacyCache[$legacyReiId] ?? null;
    }

    protected function rememberProperty(int $legacyReiId, int $propertyId): void
    {
        if (self::$propertyByLegacyCache !== null) {
            self::$propertyByLegacyCache[$legacyReiId] = $propertyId;
        }
    }

    protected function valuationRequestIdByLegacy(int $legacyRequestId): ?int
    {
        if (self::$requestByLegacyCache === null) {
            self::$requestByLegacyCache = ValuationRequest::query()->pluck('id', 'legacy_id')->map(fn ($id) => (int) $id)->all();
        }

        return self::$requestByLegacyCache[$legacyRequestId] ?? null;
    }

    protected function rememberRequest(int $legacyRequestId, int $requestId): void
    {
        if (self::$requestByLegacyCache !== null) {
            self::$requestByLegacyCache[$legacyRequestId] = $requestId;
        }
    }

    protected function mappedUserId(?int $legacyUserId): ?int
    {
        if ($legacyUserId === null || $legacyUserId <= 0) {
            return null;
        }

        if (self::$userMapCache === null) {
            self::$userMapCache = LegacyUserMap::query()
                ->pluck('user_id', 'legacy_user_id')
                ->map(fn ($id) => $id !== null ? (int) $id : null)
                ->all();
        }

        return self::$userMapCache[$legacyUserId] ?? null;
    }

    public static function clearImportCaches(): void
    {
        self::$userMapCache = null;
        self::$propertyByLegacyCache = null;
        self::$requestByLegacyCache = null;
    }
}
