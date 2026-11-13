<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClientLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:error,unhandledrejection,ajax'],
            'message' => ['required', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:1000'],
            'line' => ['nullable', 'integer', 'min:0'],
            'column' => ['nullable', 'integer', 'min:0'],
            'stack' => ['nullable', 'string', 'max:8000'],
            'page_url' => ['nullable', 'string', 'max:2000'],
            'ajax_url' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'integer', 'min:0', 'max:999'],
            'context' => ['nullable', 'array', 'max:20'],
        ];
    }
}
