<?php

declare(strict_types=1);

namespace App\Services\Valuation;

use App\Enums\Valuation\ReportExportVariant;
use App\Models\Property;
use App\Models\PropertyBorder;
use App\Models\ValuationRequest;
use App\Support\HijriDate;
use App\Support\Pdf\ReportImage;
use App\Support\Valuation\PropertyPictureMedia;
use App\Support\Valuation\Report\AttachmentImagesSection;
use App\Support\Valuation\Report\BasicDataSection;
use App\Support\Valuation\Report\BrandingSection;
use App\Support\Valuation\Report\CertificateSection;
use App\Support\Valuation\Report\ComparablesPresenter;
use App\Support\Valuation\Report\ComparablesSection;
use App\Support\Valuation\Report\CostTablePresenter;
use App\Support\Valuation\Report\CostTableSection;
use App\Support\Valuation\Report\CoverSection;
use App\Support\Valuation\Report\CustomTableSection;
use App\Support\Valuation\Report\FrameworkSection;
use App\Support\Valuation\Report\NotesSection;
use App\Support\Valuation\Report\PageBreaks;
use App\Support\Valuation\Report\PropertyInfoSection;
use App\Support\Valuation\Report\ScopeSection;
use App\Support\Valuation\Report\SignaturesPresenter;
use App\Support\Valuation\Report\SignaturesSection;
use App\Support\Valuation\Report\StandardsSection;
use App\Support\Valuation\Report\SummarySection;
use App\Support\Valuation\StaticMapImage;
use App\Support\Valuation\ValuationReportPictureHtml;

/**
 * Assembles Blade data for valuation PDF variants from imported domain models.
 *
 * @phpstan-import-type Cost from CostTablePresenter as CostTableData
 * @phpstan-import-type Signatures from SignaturesPresenter as SignaturesData
 */
final class ValuationReportDataBuilder
{
    public function __construct(
        private readonly CostTableSection $costTable = new CostTableSection,
        private readonly PropertyPictureMedia $pictureMedia = new PropertyPictureMedia,
        private readonly ValuationReportPictureHtml $pictureHtml = new ValuationReportPictureHtml,
        private readonly StaticMapImage $staticMap = new StaticMapImage,
        private readonly BrandingSection $branding = new BrandingSection,
        private readonly SignaturesSection $signatures = new SignaturesSection,
        private readonly ComparablesSection $comparables = new ComparablesSection,
        private readonly PropertyInfoSection $propertyInfo = new PropertyInfoSection,
        private readonly StandardsSection $standards = new StandardsSection,
        private readonly CustomTableSection $customTables = new CustomTableSection,
        private readonly AttachmentImagesSection $attachmentImages = new AttachmentImagesSection,
        private readonly CoverSection $cover = new CoverSection,
        private readonly CertificateSection $certificate = new CertificateSection,
        private readonly SummarySection $summary = new SummarySection,
        private readonly ScopeSection $scope = new ScopeSection,
        private readonly FrameworkSection $framework = new FrameworkSection,
        private readonly BasicDataSection $basicData = new BasicDataSection,
        private readonly PageBreaks $pageBreaks = new PageBreaks,
        private readonly ComparablesPresenter $comparablesPresenter = new ComparablesPresenter,
        private readonly CostTablePresenter $costPresenter = new CostTablePresenter,
        private readonly NotesSection $notes = new NotesSection,
        private readonly SignaturesPresenter $signaturesPresenter = new SignaturesPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(ValuationRequest $request, ReportExportVariant $variant): array
    {
        $request->loadMissing([
            'company',
            'coordinator',
            'evaluator',
            'property.location.city',
            'property.location.neighborhood',
            'property.total',
            'property.pictures',
            'property.borders',
            'property.lands',
            'property.facades',
            'property.services',
            'property.components',
            'property.comparables',
            'property.adjustments',
            'property.building',
            'property.question',
            'property.ac',
            'property.flooring',
            'customTableRows',
            'attachments',
            'subUser',
        ]);

        $property = $request->property;
        $totals = $this->costTable->totals($request);
        $amount = $totals['amount'];
        $finalFormatted = $amount !== null ? number_format($amount, 2) : '—';
        $english = $variant->locale() === 'en';
        $words = $this->costTable->inWords($amount, $english ? 'en' : 'ar');

        $border = $property?->borders->first();
        $land = $property?->lands->first();
        $facade = $property?->facades->first();
        $service = $property?->services->first();
        $location = $property?->location;

        $picturesHtml = $property instanceof Property
            ? $this->pictureHtml->render($property)
            : '';

        $reportImages = $property instanceof Property
            ? $this->pictureMedia->forPdfReport($property->pictures)
            : ['images' => [], 'missing' => [], 'notes' => []];

        $streetCount = $this->streetCount($facade, $border);

        $locationMaps = StaticMapImage::isValidPoint($location?->x_axis, $location?->y_axis)
            ? array_filter([
                'streets' => $this->staticMap->dataUri($location?->x_axis, $location?->y_axis, StaticMapImage::STREETS),
                'aerial' => $this->staticMap->dataUri($location?->x_axis, $location?->y_axis, StaticMapImage::AERIAL, 17),
            ])
            : [];

        $data = $this->sections($request, $property, $variant) + [
            'variant' => $variant,
            'request' => $request,
            'property' => $property,
            'company' => $request->company,
            'coordinator' => $request->coordinator,
            'evaluator' => $request->evaluator,
            'location' => $location,
            'border' => $border,
            'land' => $land,
            'facade' => $facade,
            'service' => $service,
            'components' => $property === null ? collect() : $property->components,
            'comparables' => $property === null ? collect() : $property->comparables,
            'adjustments' => $property === null ? collect() : $property->adjustments,
            'totals' => $totals,
            'propertyTotal' => $totals['property_total'],
            'final_amount' => $finalFormatted,
            'final_amount_raw' => $amount,
            'value_in_words' => $words,
            'is_manual_amount' => $totals['is_manual'],
            'forced_sale_percentage' => $totals['forced_sale_percentage'],
            'forced_sale_amount' => $totals['forced_sale_amount'],
            'total_area' => $totals['total_area'],
            'street_count' => $streetCount,
            'location_maps' => $locationMaps,
            'issue_date' => now()->format('d-m-Y'),
            'report_title' => $variant->label(),
            'watermark' => $variant->watermark(),
            'pictures_html' => $picturesHtml,
            'report_images' => $reportImages,
            'hide_comparisons' => (bool) ($totals['property_total']?->hide_comparisons_info_table),
            'hide_evaluation' => (bool) ($totals['property_total']?->hide_evaluation_info_table),
            'city_name' => $english
                ? ($location?->city?->name_en ?: $location?->city?->name_ar)
                : $location?->city?->name_ar,
            'neighborhood_name' => $english
                ? ($location?->neighborhood?->name_en ?: $location?->neighborhood?->name_ar)
                : $location?->neighborhood?->name_ar,
            'locale' => $english ? 'en' : 'ar',
            'direction' => $english ? 'ltr' : 'rtl',
            'reference' => $request->reference ?? $request->number ?? $request->id,
            'map_coordinates' => StaticMapImage::isValidPoint($location?->x_axis, $location?->y_axis)
                ? ['latitude' => (string) $location?->x_axis, 'longitude' => (string) $location?->y_axis]
                : null,
        ];

        return $data + ['breaks' => $this->pageBreaks->build($data)];
    }

    /**
     * Section arrays consumed by the report layout; labels follow the current app locale,
     * which the PDF generator sets to the variant's locale while building.
     *
     * @return array<string, mixed>
     */
    private function sections(ValuationRequest $request, ?Property $property, ReportExportVariant $variant): array
    {
        $locale = $variant->locale();
        $branding = $request->company === null ? null : $this->branding->build($request->company);
        $signatures = $this->signatures->build($request);
        $cover = $this->cover->build($request, $property);
        $comparables = $property === null ? null : $this->comparables->build($property);
        $cost = $property === null ? null : $this->costTable->build($property, $locale);

        return $this->valuationSections($request, $property, $cost, $signatures, $branding, $variant) + [
            'branding' => $branding,
            'page_footer' => $this->branding->footer($branding),
            'cover' => $cover,
            'certificate' => $this->certificate->build($request, $property, $branding, $signatures['evaluator']),
            'summary' => $this->summary->build($request, $property, $branding['name'] ?? '', $signatures),
            'scope' => $this->scope->build($property, $cover),
            'framework' => $this->framework->build($locale),
            'basic_data' => $this->basicData->build($request, $property),
            'signatures' => $signatures,
            'cost' => $cost,
            'comparables_section' => $comparables,
            'market_approach' => $this->comparablesPresenter->build($comparables),
            'property_cards' => $property === null ? [] : $this->propertyInfo->build($property),
            'standards' => $property === null ? [] : $this->standards->build($property),
            'custom_tables' => $this->customTables->build($request),
            'attachment_images' => $this->attachmentImages->build($request),
            'static_pages' => $variant->isFull() ? $this->staticPages($locale) : [],
            'show_signatures' => $variant->showsSignatures(),
            'hijri_issue_date' => HijriDate::fromGregorian(now())->numeric(),
        ];
    }

    /**
     * Cost table, final value, notes and signatures printed after the market approach.
     *
     * @param  CostTableData|null  $cost
     * @param  SignaturesData  $signatures
     * @param  array{stamp: ?string}|null  $branding
     * @return array<string, mixed>
     */
    private function valuationSections(ValuationRequest $request, ?Property $property, ?array $cost, array $signatures, ?array $branding, ReportExportVariant $variant): array
    {
        return [
            'cost_table' => $this->costPresenter->table($cost, (bool) $property?->total?->hide_evaluation_info_table),
            'final_value' => $this->costPresenter->finalValue($cost),
            'notes' => $this->notes->build($property),
            'signature_block' => $this->signaturesPresenter->build($signatures, $branding['stamp'] ?? null, $request->ended_at, $variant->showsSignatures()),
        ];
    }

    /**
     * @return list<string>
     */
    private function staticPages(string $locale): array
    {
        $pages = [];
        foreach ((array) config("report_branding.assets.static_pages.{$locale}", []) as $relative) {
            $src = ReportImage::fromPath(public_path((string) $relative));
            if ($src !== null) {
                $pages[] = $src;
            }
        }

        return $pages;
    }

    private function streetCount(mixed $facade, ?PropertyBorder $border): int
    {
        $count = 0;
        if ($facade !== null) {
            foreach (['facade_type_n', 'facade_type_s', 'facade_type_e', 'facade_type_w'] as $field) {
                if (! empty($facade->{$field})) {
                    $count++;
                }
            }
        }

        if ($count > 0 || $border === null) {
            return $count;
        }

        foreach (['north', 'south', 'east', 'west'] as $dir) {
            $value = (string) ($border->{$dir} ?? '');
            if ($value !== '' && (str_contains($value, 'شارع') || str_contains($value, 'Street'))) {
                $count++;
            }
        }

        return $count;
    }
}
