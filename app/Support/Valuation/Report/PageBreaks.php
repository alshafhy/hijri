<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

/**
 * Whether each section of the main report pages opens with a page break: only when something is already
 * printed before it, so the main pages never start with an empty page.
 */
final class PageBreaks
{
    /** Sections in print order => whether the section starts on a new page (false = flows after the previous one). */
    private const SECTIONS = [
        'certificate' => true,
        'static_pages' => true,
        'summary' => true,
        'scope' => true,
        'framework' => true,
        'basic_data' => true,
        'property_cards' => false,
        'location_maps' => true,
    ];

    /**
     * @param  array<string, mixed>  $data  report view data
     * @return array<string, bool>
     */
    public function build(array $data): array
    {
        $breaks = [];
        $printedBefore = false;
        $previousPrinted = false;

        foreach (self::SECTIONS as $key => $startsPage) {
            $breaks[$key] = $printedBefore && ($startsPage || ! $previousPrinted);
            $previousPrinted = ($data[$key] ?? null) !== null && ($data[$key] ?? null) !== [];
            $printedBefore = $printedBefore || $previousPrinted;
        }

        return $breaks;
    }
}
