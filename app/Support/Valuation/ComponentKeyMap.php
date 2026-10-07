<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use Illuminate\Support\Str;

/**
 * Maps legacy building_areas / building_price column names to property_components keys.
 */
final class ComponentKeyMap
{
    /**
     * Legacy area column => [legacy price column, component key].
     *
     * @var array<string, array{0:string,1:string}>
     */
    public const MAP = [
        'land_area' => ['price_meter', 'land_area'],
        'floorG' => ['pfloorG', 'floor_g'],
        'floorA' => ['pfloorA', 'floor_a'],
        'floorB' => ['pfloorB', 'floor_b'],
        'floorU' => ['pfloorU', 'floor_u'],
        'repeated_floors' => ['prepeated_floors', 'repeated_floors'],
        'repeated_No' => ['prepeated_No', 'repeated_no'],
        'flat_area' => ['pflat_area', 'flat_area'],
        'share_land' => ['pshare_land', 'share_land'],
        'fence' => ['pfence', 'fence'],
        'pool' => ['ppool', 'pool'],
        'annexe' => ['pannexe', 'annexe'],
        'parking' => ['pparking', 'parking'],
        'land_garden' => ['pland_garden', 'land_garden'],
        'basement' => ['pbasement', 'basement'],
        'shop' => ['pshop', 'shop'],
        'apartment' => ['papartment', 'apartment'],
        'office' => ['poffice', 'office'],
        'exhibition' => ['pexhibition', 'exhibition'],
        'warehouse' => ['pwarehouse', 'warehouse'],
        'pond' => ['ppond', 'pond'],
        'silo' => ['psilo', 'silo'],
        'vertical_tank' => ['pvertical_tank', 'vertical_tank'],
        'chalets' => ['pchalets', 'chalets'],
        'children_space' => ['pchildren_space', 'children_space'],
        'supplies' => ['psupplies', 'supplies'],
        'restaurant' => ['prestaurant', 'restaurant'],
        'mosque' => ['pmosque', 'mosque'],
        'buffet' => ['pbuffet', 'buffet'],
        'workers_places' => ['pworkers_places', 'workers_places'],
        'fuel_tank' => ['pfuel_tank', 'fuel_tank'],
        'break' => ['pbreak', 'break'],
        'water_tank' => ['pwater_tank', 'water_tank'],
        'electric_and_mechanical' => ['pelectric_and_mechanical', 'electric_and_mechanical'],
        'oil' => ['poil', 'oil'],
        'tire' => ['ptire', 'tire'],
        'share_loft' => ['pshare_loft', 'share_loft'],
        'loft_area' => ['ploft_area', 'loft_area'],
        'earnings' => ['pearnings', 'earnings'],
        'depreciation' => ['pdepreciation', 'depreciation'],
        'discount' => ['pdiscount', 'discount'],
        'cost' => ['pcost', 'cost'],
        'share_floor' => ['pshare_floor', 'share_floor'],
        'floor_area' => ['pfloor_area', 'floor_area'],
        'grass' => ['pgrass', 'grass'],
        'upper' => ['pupper', 'upper'],
        'ground' => ['pground', 'ground'],
        'base_area' => ['base_area', 'base_area'],
        'ground_commercial' => ['pground_commercial', 'ground_commercial'],
        'first_commercial' => ['pfirst_commercial', 'first_commercial'],
        'ground_floor' => ['pground_floor', 'ground_floor'],
        'first_office' => ['pfirst_office', 'first_office'],
        'first_basement' => ['pfirst_basement', 'first_basement'],
        'second_basement' => ['psecond_basement', 'second_basement'],
        'electricity_room' => ['pelectricity_room', 'electricity_room'],
        'kiosk' => ['pkiosk', 'kiosk'],
        'ground_office' => ['pground_office', 'ground_office'],
    ];

    public static function fromAreaColumn(string $areaColumn): string
    {
        return self::MAP[$areaColumn][1] ?? Str::snake($areaColumn);
    }
}
