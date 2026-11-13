<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

/**
 * Legacy `commercial` rows → property_buildings (kind=commercial).
 */
final class CommercialBuildingsImporter extends BuildingsImporter
{
    public static function key(): string
    {
        return 'commercial_buildings';
    }

    protected function kind(): string
    {
        return 'commercial';
    }
}
