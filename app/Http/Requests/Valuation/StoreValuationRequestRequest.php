<?php

declare(strict_types=1);

namespace App\Http\Requests\Valuation;

use App\Actions\Valuation\CreateValuationRequestAction;
use App\Http\Requests\Valuation\Concerns\ValidatesInitialData;
use App\Models\Contractor;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreValuationRequestRequest extends FormRequest
{
    use ValidatesInitialData;

    public function authorize(): bool
    {
        return $this->user()?->can('create', ValuationRequest::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge($this->initialDataRules(), $this->propertyTypeRules(), [
            'company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')],
            'coordinator_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'client_type' => ['required', 'integer', Rule::in([CreateValuationRequestAction::CLIENT_CONTRACTOR, CreateValuationRequestAction::CLIENT_PARTNER])],
            'contractor_id' => [
                'nullable', 'required_if:client_type,'.CreateValuationRequestAction::CLIENT_CONTRACTOR, 'integer',
                Rule::exists('contractors', 'id')->where('state', Contractor::STATE_ACTIVE),
            ],
            'partner_id' => [
                'nullable', 'required_if:client_type,'.CreateValuationRequestAction::CLIENT_PARTNER, 'integer',
                Rule::exists('partners', 'id')->where('state', Partner::STATE_ACTIVE),
            ],
            'offer_estate_id' => ['nullable', 'integer', Rule::exists('offer_estates', 'id')],
            'coordinator_share' => ['nullable', 'integer', 'min:0', 'max:100'],
            'evaluator_share' => ['nullable', 'integer', 'min:0', 'max:100'],
            'manager_share' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $this->validateNeighborhoodBelongsToCity($validator);

        $validator->after(function (Validator $validator): void {
            $estateId = $this->input('offer_estate_id');
            if ($estateId === null || $estateId === '' || $validator->errors()->has('offer_estate_id')) {
                return;
            }
            $belongs = (int) $this->input('client_type') === CreateValuationRequestAction::CLIENT_PARTNER
                && OfferEstate::query()->whereKey($estateId)
                    ->whereHas('offer', fn ($q) => $q->where('partner_id', $this->input('partner_id')))
                    ->exists();
            if (! $belongs) {
                $validator->errors()->add('offer_estate_id', __('The selected estate does not belong to the client offers.'));
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'x_axis' => __('Latitude'),
            'y_axis' => __('Longitude'),
            'contractor_id' => __('Contractor'),
            'partner_id' => __('Partner'),
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'x_axis.required' => __('The property location was not selected on the map.'),
            'y_axis.required' => __('The property location was not selected on the map.'),
        ];
    }
}
