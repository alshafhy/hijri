<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\Property;
use App\Models\PropertyAdjustment;
use App\Models\PropertyComparable;
use App\Models\PropertyComponent;
use App\Models\PropertyPicture;
use App\Models\PropertyTotal;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationCustomTableRow;
use App\Models\ValuationRequest;
use App\Support\Valuation\ComparablesCalculator;
use App\Support\Valuation\CostApproachCalculator;
use App\Support\Valuation\ValuationActivity;
use App\Support\Valuation\ValuationFieldGrid;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class UpdateEvaluatorDataAction
{
    private const PROPERTY_FIELDS = [
        'customer_name', 'owner_name', 'customer_name_en', 'owner_name_en', 'property_kind', 'property_type',
        'instrument_no', 'instrument_date', 'instrument_gregorian_date', 'notes', 'notes_property',
        'notes_real_estate', 'valuation_way_type', 'base_value', 'note_base_value', 'note_valuation_way_type',
        'forced_sale_percentage', 'evaluation_date', 'evaluation_date_hijri', 'value_assumption',
        'valuation_usage', 'valuation_usage_desc', 'valuation_type', 'valuation_type_desc',
        'market_valuation_way_main', 'market_valuation_way_sub', 'income_valuation_way_main',
        'income_valuation_way_sub', 'cost_valuation_way_main', 'cost_valuation_way_sub',
        'type_of_getings_value', 'requested_papers', 'assumptions', 'area_of_search', 'area_approval_way',
        'informations_source', 'important_assumptions', 'conclusion_value', 'other_users', 'deposit_number',
    ];

    private const LAND_LIST_FIELDS = ['facade', 'door_ex', 'door_in', 'street', 'neighbor', 'include', 'surrounding_facilities'];

    private const COMPARABLE_ADJUSTMENT_FIELDS = [
        'market_conditions_value', 'market_conditions_percentage', 'financing_terms_value', 'financing_terms_percentage',
        'sale_terms_value', 'sale_terms_percentage', 'settlement_ratio', 'area_percentage',
        'location_status_value', 'location_status_percentage', 'easy_access_value', 'easy_access_percentage',
        'main_street_width_value', 'main_street_width_percentage', 'streets_count_value', 'streets_count_percentage',
        'land_shape_value', 'land_shape_percentage', 'land_topography_value', 'land_topography_percentage',
        'natural_factors_value', 'natural_factors_percentage', 'near_main_street_value', 'near_main_street_percentage',
        'other_value', 'other_percentage', 'proportional_adjustment',
    ];

    /** @var list<string> */
    private array $storedFiles = [];

    public function __construct(
        private readonly CostApproachCalculator $costCalculator,
        private readonly ComparablesCalculator $comparablesCalculator,
    ) {}

    /**
     * Sections listed in `sections` are fully replaced even when their inputs are empty
     * (unchecked checkboxes are not posted); other sections are only touched when present.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, ValuationRequest $request, array $data): ValuationRequest
    {
        Gate::forUser($actor)->authorize('update', $request);

        $sections = array_flip((array) ($data['sections'] ?? []));
        $has = static fn (string $key): bool => isset($sections[$key]) || array_key_exists($key, $data);

        try {
            return DB::transaction(function () use ($actor, $request, $data, $has): ValuationRequest {
                $property = $request->property;
                abort_if($property === null, 404);

                $property->fill(array_intersect_key($data, array_flip(self::PROPERTY_FIELDS)))->save();

                if (isset($data['location']) && is_array($data['location'])) {
                    $this->saveLocation($property, $data['location']);
                }
                if ($has('land')) {
                    $this->saveLand($property, (array) ($data['land'] ?? []));
                }
                if ($has('facades')) {
                    $this->saveFacades($property, (array) ($data['facades'] ?? []));
                }
                if ($has('services')) {
                    $this->saveServices($property, (array) ($data['services'] ?? []));
                }
                if ($has('flooring')) {
                    $this->saveFlooring($property, (array) ($data['flooring'] ?? []));
                }
                if ($has('building')) {
                    $this->saveBuilding($property, (array) ($data['building'] ?? []));
                }
                if ($has('ac')) {
                    $this->saveAc($property, (array) ($data['ac'] ?? []));
                }
                if ($has('question')) {
                    $property->question()->updateOrCreate(['property_id' => $property->id], (array) ($data['question'] ?? []));
                }
                if ($has('components')) {
                    $this->saveComponents($property, (array) ($data['components'] ?? []));
                }
                if ($has('components') || $has('totals')) {
                    $this->recalculateTotals($property->fresh() ?? $property, (array) ($data['totals'] ?? []));
                }
                if ($has('comparables')) {
                    $this->saveComparables($property, $data);
                }
                if ($has('custom_table')) {
                    $this->saveCustomTable($request, (array) ($data['custom_table'] ?? []));
                }

                $this->savePictures($request, $property, $data);
                $this->saveAttachments($actor, $request, (array) ($data['new_attachments'] ?? []));

                ValuationActivity::log($actor, $request, 'evaluator_data_updated', 'Evaluator dossier data updated');

                return $request->fresh(['property.location', 'property.components', 'property.total']) ?? $request;
            });
        } catch (Throwable $e) {
            foreach ($this->storedFiles as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $location
     */
    private function saveLocation(Property $property, array $location): void
    {
        $property->location()->updateOrCreate(
            ['property_id' => $property->id],
            array_intersect_key($location, array_flip([
                'city_id', 'neighborhood_id', 'street', 'sketch_no', 'part_no',
                'piece_number', 'x_axis', 'y_axis', 'sketch_name',
            ]))
        );
    }

    /**
     * @param  array<string, mixed>  $land
     */
    private function saveLand(Property $property, array $land): void
    {
        foreach (self::LAND_LIST_FIELDS as $field) {
            $land[$field] = $this->joinList($land[$field] ?? null);
        }
        $record = $property->lands()->first();
        $record ? $record->update($land) : $property->lands()->create($land);
    }

    /**
     * @param  array<string, mixed>  $facades
     */
    private function saveFacades(Property $property, array $facades): void
    {
        $attributes = [];
        foreach (array_keys(config('valuation_form.facade_sides')) as $side) {
            $attributes['facade_type_'.$side] = $this->joinList($facades[$side] ?? null);
        }
        $record = $property->facades()->first();
        $record ? $record->update($attributes) : $property->facades()->create($attributes);
    }

    /**
     * @param  list<string>  $checked
     */
    private function saveServices(Property $property, array $checked): void
    {
        $attributes = [];
        foreach (array_keys(config('valuation_form.services')) as $service) {
            $attributes[$service] = in_array($service, $checked, true) ? 'نعم' : 'لا';
        }
        $record = $property->services()->first();
        $record ? $record->update($attributes) : $property->services()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $flooring
     */
    private function saveFlooring(Property $property, array $flooring): void
    {
        $attributes = [];
        foreach (array_keys(config('valuation_form.flooring_rooms')) as $room) {
            $attributes["flooring_{$room}"] = $this->joinList($flooring[$room] ?? null);
            $attributes["{$room}_description"] = $flooring["{$room}_description"] ?? null;
        }
        $property->flooring()->updateOrCreate(['property_id' => $property->id], $attributes);
    }

    /**
     * @param  array<string, mixed>  $building
     */
    private function saveBuilding(Property $property, array $building): void
    {
        $building['kind'] = trim((string) $property->property_kind) === 'سكني' ? 'residential' : 'commercial';
        $property->building()->updateOrCreate(['property_id' => $property->id], $building);
    }

    /**
     * @param  array<string, mixed>  $ac
     */
    private function saveAc(Property $property, array $ac): void
    {
        $property->ac()->updateOrCreate(['property_id' => $property->id], [
            'types' => $this->joinList($ac['types'] ?? null),
            'ac_count' => $ac['ac_count'] ?? null,
        ]);
    }

    /**
     * @param  array<string, array{area_value?: mixed, price_value?: mixed}>  $components
     */
    private function saveComponents(Property $property, array $components): void
    {
        foreach ($components as $key => $values) {
            if (! is_string($key) || $key === '') {
                continue;
            }
            PropertyComponent::query()->updateOrCreate(
                ['property_id' => $property->id, 'component_key' => $key],
                [
                    'area_value' => $values['area_value'] ?? null,
                    'price_value' => $values['price_value'] ?? null,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $totals
     */
    private function recalculateTotals(Property $property, array $totals): void
    {
        $property->load(['components', 'building', 'total']);
        $existing = $property->total;
        $byKey = $property->components->keyBy('component_key');

        $rows = [];
        foreach (ValuationFieldGrid::rows($property->property_kind, $property->property_type) as $row) {
            $component = $byKey->get($row['key']);
            $rows[$row['key']] = [
                'area' => $component?->area_value,
                'price' => $component?->price_value,
            ];
        }

        $profit = $totals['profit_percentage'] ?? $existing?->profit_percentage;
        $depreciationType = $totals['depreciation_type'] ?? $existing?->depreciation_type;
        $movables = $totals['movables'] ?? $existing?->movables;

        $result = $this->costCalculator->calculate($rows, [
            'profit_percentage' => $profit,
            'depreciation_type' => $depreciationType,
            'build_age' => $property->building?->build_age,
            'default_build_age' => $property->building?->default_build_age,
            'remaining_build_age' => $property->building?->remaining_build_age,
            'movables' => $movables,
            'value_assumption' => $property->value_assumption,
            'forced_sale_percentage' => $property->forced_sale_percentage,
        ]);

        PropertyTotal::query()->updateOrCreate(['property_id' => $property->id], [
            'profit_percentage' => $profit === '' ? null : $profit,
            'profit_amount' => $result['profit_amount'],
            'depreciation_type' => $depreciationType === '' ? null : $depreciationType,
            'depreciation_amount' => $result['depreciation_amount'],
            'calc_amount' => $result['calc_amount'],
            'total_amount' => $result['total_amount'],
            'total_area' => $result['total_area'],
            'movables' => $result['movables'],
            'forced_sale_percentage' => $property->forced_sale_percentage,
            'forced_sale_amount' => $result['forced_sale_amount'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveComparables(Property $property, array $data): void
    {
        $input = [];
        foreach ((array) ($data['comparables'] ?? []) as $sequence => $comparable) {
            $sequence = (int) $sequence;
            if ($sequence < 1 || $sequence > 4 || ! is_array($comparable)) {
                continue;
            }
            $filled = array_filter($comparable, static fn ($v): bool => $v !== null && $v !== '');
            if ($filled === []) {
                PropertyComparable::query()->where('property_id', $property->id)->where('sequence', $sequence)->delete();
                PropertyAdjustment::query()->where('property_id', $property->id)->where('type', $sequence)->delete();

                continue;
            }
            $input[$sequence] = $comparable;
        }

        $calculated = $this->comparablesCalculator->calculate($input);
        $source = $data['comparison_information_source'] ?? null;

        foreach ($input as $sequence => $c) {
            PropertyComparable::query()->updateOrCreate(
                ['property_id' => $property->id, 'sequence' => $sequence],
                [
                    'real_estate_type' => $c['real_estate_type'] ?? null,
                    'street_width' => $c['street_width'] ?? null,
                    'front_type' => $c['front_type'] ?? null,
                    'area' => $c['area'] ?? null,
                    'price' => $c['price'] ?? null,
                    'coordinates_url' => $c['coordinates_url'] ?? null,
                    'information_source' => $source,
                ]
            );

            $row = $calculated['rows'][$sequence];
            PropertyAdjustment::query()->updateOrCreate(
                ['property_id' => $property->id, 'type' => $sequence],
                array_merge(
                    array_intersect_key($c, array_flip(self::COMPARABLE_ADJUSTMENT_FIELDS)),
                    [
                        'transaction_date' => $c['transaction_date'] ?? null,
                        'transaction_gregorian_date' => $c['transaction_gregorian_date'] ?? null,
                        'total_adjustments_value' => $row['first_value'] + $row['second_value'],
                        'total_adjustments_price' => $row['final_price'],
                        'meter_price' => $calculated['meter_price'],
                        'meter_price_approximately' => $data['meter_price_approximately'] ?? null,
                    ]
                )
            );
        }
    }

    /**
     * @param  array<string, mixed>  $table
     */
    private function saveCustomTable(ValuationRequest $request, array $table): void
    {
        ValuationCustomTableRow::query()->where('valuation_request_id', $request->id)->delete();

        $title = $table['title'] ?? null;
        $order = 0;
        foreach ((array) ($table['rows'] ?? []) as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $values = array_values(array_map(static fn ($v): string => (string) $v, (array) ($row['values'] ?? [])));
            if ($label === '' && array_filter($values, static fn (string $v): bool => $v !== '') === []) {
                continue;
            }
            ValuationCustomTableRow::query()->create([
                'valuation_request_id' => $request->id,
                'table_title' => $title,
                'label' => $label,
                'values' => $values,
                'sort_order' => $order++,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function savePictures(ValuationRequest $request, Property $property, array $data): void
    {
        $existing = $property->pictures()->get()->keyBy('id');

        foreach ((array) ($data['picture_descriptions'] ?? []) as $id => $description) {
            $existing->get((int) $id)?->update(['description' => $description]);
        }

        $order = array_values(array_filter(array_map('intval', explode(',', (string) ($data['picture_order'] ?? '')))));
        foreach ($order as $position => $id) {
            $existing->get($id)?->update(['sort_order' => $position]);
        }

        $next = (int) $property->pictures()->max('sort_order') + 1;
        $descriptions = (array) ($data['new_picture_descriptions'] ?? []);
        foreach ((array) ($data['new_pictures'] ?? []) as $index => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $path = $this->store($file, 'valuation-pictures/'.$request->id);
            PropertyPicture::query()->create([
                'property_id' => $property->id,
                'filename' => basename($path),
                'description' => $descriptions[$index] ?? null,
                'sort_order' => $next++,
                'relative_path' => $path,
                'file_exists' => true,
            ]);
        }
    }

    /**
     * @param  array<int, array{file?: mixed, type?: mixed}>  $attachments
     */
    private function saveAttachments(User $actor, ValuationRequest $request, array $attachments): void
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

    private function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'local');
        if ($path === false) {
            throw new \RuntimeException('Unable to store uploaded file.');
        }
        $this->storedFiles[] = $path;

        return $path;
    }

    private function joinList(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = implode(',', array_filter(array_map('trim', array_map('strval', $value)), static fn (string $v): bool => $v !== ''));
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
