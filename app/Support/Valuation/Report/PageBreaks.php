<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

/**
 * Whether each section of the main report pages opens with a page break: only when something is already
 * printed before it, so the main pages never start with an empty page.
 */
final class PageBreaks
{
    /** Starts on a new page. */
    private const string NEW_PAGE = 'new_page';

    /** Flows after the previous section; starts a new page when that section is absent. */
    private const string AFTER_PREVIOUS = 'after_previous';

    /** Flows after whatever was printed before it. */
    private const string FLOW = 'flow';

    /** Sections in print order => how the section starts. */
    private const array SECTIONS = [
        'certificate' => self::NEW_PAGE,
        'static_pages' => self::NEW_PAGE,
        'summary' => self::NEW_PAGE,
        'scope' => self::NEW_PAGE,
        'framework' => self::NEW_PAGE,
        'basic_data' => self::NEW_PAGE,
        'property_cards' => self::AFTER_PREVIOUS,
        'location_maps' => self::NEW_PAGE,
        'market_approach' => self::NEW_PAGE,
        'cost_table' => self::FLOW,
        'custom_tables' => self::FLOW,
        'final_value' => self::FLOW,
        'notes' => self::FLOW,
        'signature_block' => self::FLOW,
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

        foreach (self::SECTIONS as $key => $start) {
            $breaks[$key] = $printedBefore && match ($start) {
                self::NEW_PAGE => true,
                self::AFTER_PREVIOUS => ! $previousPrinted,
                self::FLOW => false,
            };
            $previousPrinted = ($data[$key] ?? null) !== null && ($data[$key] ?? null) !== [];
            $printedBefore = $printedBefore || $previousPrinted;
        }

        return $breaks;
    }
}
