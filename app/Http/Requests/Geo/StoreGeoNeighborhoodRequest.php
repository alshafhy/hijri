<?php

declare(strict_types=1);

namespace App\Http\Requests\Geo;

use App\Models\GeoNeighborhood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGeoNeighborhoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GeoNeighborhood::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'city_id' => ['required', 'integer', Rule::exists('geo_cities', 'id')],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
        ];
    }
}
