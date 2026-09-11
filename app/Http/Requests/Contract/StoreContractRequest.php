<?php

declare(strict_types=1);

namespace App\Http\Requests\Contract;

use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Contract::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contractor_id' => ['required', 'integer', Rule::exists('contractors', 'id')],
            'valuation_request_id' => ['required', 'integer', Rule::exists('valuation_requests', 'id')],
            'state' => ['nullable', 'integer', Rule::in([Contract::STATE_UNPAID, Contract::STATE_PAID])],
        ];
    }
}
