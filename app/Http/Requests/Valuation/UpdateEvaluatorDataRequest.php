<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use App\Support\Valuation\ComparablesCalculator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluatorDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('update', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:65000'];
        $short = ['nullable', 'string', 'max:255'];
        $num = ['nullable', 'numeric'];
        $int = ['nullable', 'integer'];
        $upload = config('valuation_form.upload');

        $rules = [
            'sections' => ['nullable', 'array'],
            'sections.*' => ['string', 'max:40'],
            'customer_name' => ['nullable', 'string', 'max:128'],
            'owner_name' => ['nullable', 'string', 'max:128'],
            'customer_name_en' => ['nullable', 'string', 'max:250'],
            'owner_name_en' => ['nullable', 'string', 'max:200'],
            'property_kind' => ['nullable', 'string', 'max:45'],
            'property_type' => ['nullable', 'string', 'max:32'],
            'instrument_no' => ['nullable', 'string', 'max:200'],
            'instrument_date' => ['nullable', 'string', 'max:64'],
            'instrument_gregorian_date' => ['nullable', 'string', 'max:64'],
            'notes' => $text,
            'notes_property' => $text,
            'notes_real_estate' => $text,
            'valuation_way_type' => $text,
            'base_value' => ['nullable', 'string', 'max:100'],
            'forced_sale_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'evaluation_date' => ['nullable', 'date'],
            'evaluation_date_hijri' => ['nullable', 'string', 'max:64'],
            'value_assumption' => $int,
            'valuation_usage' => $int,
            'valuation_usage_desc' => ['nullable', 'string', 'max:250'],
            'valuation_type' => $int,
            'valuation_type_desc' => $text,
            'note_base_value' => $text,
            'note_valuation_way_type' => $text,
            'market_valuation_way_main' => $int,
            'market_valuation_way_sub' => $int,
            'income_valuation_way_main' => $int,
            'income_valuation_way_sub' => $int,
            'cost_valuation_way_main' => $int,
            'cost_valuation_way_sub' => $int,
            'type_of_getings_value' => $text,
            'requested_papers' => $text,
            'assumptions' => $text,
            'area_of_search' => $text,
            'area_approval_way' => $text,
            'informations_source' => $text,
            'important_assumptions' => $text,
            'conclusion_value' => $text,
            'other_users' => $text,
            'deposit_number' => ['nullable', 'string', 'max:200'],

            'location' => ['nullable', 'array'],
            'location.city_id' => ['nullable', 'integer', Rule::exists('geo_cities', 'id')],
            'location.neighborhood_id' => ['nullable', 'integer', Rule::exists('geo_neighborhoods', 'id')],
            'location.street' => ['nullable', 'string', 'max:45'],
            'location.sketch_no' => ['nullable', 'string', 'max:45'],
            'location.part_no' => ['nullable', 'string', 'max:45'],
            'location.piece_number' => ['nullable', 'string', 'max:100'],
            'location.x_axis' => ['nullable', 'string', 'max:45'],
            'location.y_axis' => ['nullable', 'string', 'max:45'],
            'location.sketch_name' => ['nullable', 'string', 'max:45'],

            'land' => ['nullable', 'array'],
            'land.site' => ['nullable', 'string', 'max:45'],
            'land.land_nature' => ['nullable', 'string', 'max:45'],
            'land.facade' => ['nullable', 'array'],
            'land.door_ex' => ['nullable', 'array'],
            'land.door_in' => ['nullable', 'array'],
            'land.street' => ['nullable', 'array'],
            'land.neighbor' => ['nullable', 'array'],
            'land.include' => ['nullable', 'array'],
            'land.surrounding_facilities' => ['nullable', 'array'],
            'land.*.*' => ['nullable', 'string', 'max:100'],
            'land.heater_no' => ['nullable', 'string', 'max:45'],
            'land.lift_no' => ['nullable', 'string', 'max:45'],
            'land.bathroom_a_no' => ['nullable', 'string', 'max:45'],
            'land.bathroom_e_no' => ['nullable', 'string', 'max:45'],
            'land.electricity_no' => ['nullable', 'string', 'max:45'],
            'land.electricity_num' => ['nullable', 'string', 'max:45'],
            'land.water_no' => ['nullable', 'string', 'max:45'],
            'land.water_num' => ['nullable', 'string', 'max:45'],
            'land.ownership_type' => $int,
            'land.furnishing_status' => $int,
            'land.building_type' => $int,
            'land.finishing_status' => $int,
            'land.property_use' => ['nullable', 'string', 'max:100'],
            'land.street_width' => $num,
            'land.total_building_area' => $num,
            'land.additional_features' => ['nullable', 'string', 'max:100'],
            'land.approved_floors' => $num,
            'land.approved_building' => $num,
            'land.sector_description' => ['nullable', 'string', 'max:100'],
            'land.ownership_description' => ['nullable', 'string', 'max:100'],

            'facades' => ['nullable', 'array'],
            'facades.*' => ['nullable', 'array'],
            'facades.*.*' => ['nullable', 'string', 'max:50'],
            'services' => ['nullable', 'array'],
            'services.*' => ['nullable', 'string', Rule::in(array_keys(config('valuation_form.services')))],
            'flooring' => ['nullable', 'array'],

            'building' => ['nullable', 'array'],
            'building.build_age' => ['nullable', 'string', 'max:20'],
            'building.default_build_age' => ['nullable', 'integer', 'min:0'],
            'building.remaining_build_age' => ['nullable', 'integer', 'min:0'],
            'building.architectural' => ['nullable', 'string', 'max:45'],
            'building.quality_insulation' => ['nullable', 'string', 'max:45'],
            'building.structure_construction' => ['nullable', 'string', 'max:45'],
            'building.bishop_type' => ['nullable', 'string', 'max:45'],
            'building.build_state' => ['nullable', 'string', 'max:45'],
            'ac' => ['nullable', 'array'],
            'ac.types' => ['nullable', 'array'],
            'ac.types.*' => ['nullable', 'string', 'max:50'],
            'ac.ac_count' => ['nullable', 'integer', 'min:0'],

            'question' => ['nullable', 'array'],
            'question.best_use' => ['nullable', Rule::in(['0', '1', 0, 1])],
            'question.cost' => ['nullable', 'string', 'max:45'],
            'question.need_cost' => ['nullable', Rule::in(config('valuation_form.yes_no'))],
            'question.inside_inspection' => ['nullable', Rule::in(config('valuation_form.yes_no'))],
            'question.outside_inspection' => ['nullable', Rule::in(config('valuation_form.yes_no'))],

            'components' => ['nullable', 'array'],
            'components.*.area_value' => $num,
            'components.*.price_value' => $num,
            'totals' => ['nullable', 'array'],
            'totals.profit_percentage' => $num,
            'totals.depreciation_type' => ['nullable', Rule::in(['', '1', '2', 1, 2])],
            'totals.movables' => $num,

            'comparison_information_source' => $text,
            'meter_price_approximately' => $num,
            'comparables' => ['nullable', 'array', 'max:4'],
            'comparables.*.real_estate_type' => $short,
            'comparables.*.transaction_date' => ['nullable', 'date'],
            'comparables.*.transaction_gregorian_date' => ['nullable', 'date'],
            'comparables.*.front_type' => ['nullable', 'string', 'max:200'],
            'comparables.*.street_width' => $int,
            'comparables.*.area' => $num,
            'comparables.*.price' => $num,
            'comparables.*.coordinates_url' => ['nullable', 'string', 'max:255'],
            'comparables.*.proportional_adjustment' => $int,
            'comparables.*.market_conditions_value' => $short,
            'comparables.*.financing_terms_value' => $short,
            'comparables.*.sale_terms_value' => $short,
            'comparables.*.location_status_value' => $int,
            'comparables.*.easy_access_value' => $int,
            'comparables.*.main_street_width_value' => $int,
            'comparables.*.streets_count_value' => $int,
            'comparables.*.land_shape_value' => $int,
            'comparables.*.land_topography_value' => $short,
            'comparables.*.natural_factors_value' => $short,
            'comparables.*.near_main_street_value' => $int,
            'comparables.*.other_value' => $short,

            'custom_table' => ['nullable', 'array'],
            'custom_table.title' => $short,
            'custom_table.rows' => ['nullable', 'array', 'max:100'],
            'custom_table.rows.*.label' => $short,
            'custom_table.rows.*.values' => ['nullable', 'array', 'max:20'],
            'custom_table.rows.*.values.*' => $short,

            'new_pictures' => ['nullable', 'array', 'max:50'],
            'new_pictures.*' => ['image', 'max:'.$upload['picture_max_kb']],
            'new_picture_descriptions' => ['nullable', 'array'],
            'new_picture_descriptions.*' => ['nullable', 'string', 'max:64'],
            'picture_order' => ['nullable', 'string', 'max:5000'],
            'picture_descriptions' => ['nullable', 'array'],
            'picture_descriptions.*' => ['nullable', 'string', 'max:64'],

            'new_attachments' => ['nullable', 'array', 'max:30'],
            'new_attachments.*.file' => ['nullable', 'file', 'mimes:'.$upload['attachment_mimes'], 'max:'.$upload['attachment_max_kb']],
            'new_attachments.*.type' => ['nullable', 'string', 'max:255'],
        ];

        foreach (array_merge(ComparablesCalculator::TRANSACTION_PERCENTAGES, ComparablesCalculator::PROPERTY_PERCENTAGES) as $field) {
            $rules["comparables.*.{$field}"] = $num;
        }
        foreach (array_keys(config('valuation_form.building_questions')) as $field) {
            $rules["question.{$field}"] = ['nullable', Rule::in(config('valuation_form.yes_no'))];
        }
        foreach (array_keys(config('valuation_form.flooring_rooms')) as $room) {
            $rules["flooring.{$room}"] = ['nullable', 'array'];
            $rules["flooring.{$room}.*"] = ['nullable', 'string', 'max:50'];
            $rules["flooring.{$room}_description"] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }
}
