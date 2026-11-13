<?php

declare(strict_types=1);

namespace App\Http\Requests\Geo;

use App\Models\GeoCity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGeoCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GeoCity::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255', Rule::unique('geo_cities', 'name_ar')],
            'name_en' => ['nullable', 'string', 'max:255'],
        ];
    }
}
