<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeEvaluatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $valuation = $this->route('valuationRequest');

        return $this->user()?->can('changeEvaluator', $valuation) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'evaluator_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
