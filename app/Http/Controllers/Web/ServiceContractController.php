<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Pdf\RenderDocumentPdfAction;
use App\Actions\ServiceContract\CreateServiceContractAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceContract\StoreServiceContractRequest;
use App\Models\GeoCity;
use App\Models\ServiceContract;
use App\Support\HijriDate;
use App\Support\Valuation\ArabicAmountInWords;
use App\Support\Valuation\ValuationFieldGrid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ServiceContractController extends Controller
{
    public function index(): View
    {
        $contracts = ServiceContract::query()
            ->with(['city:id,name_ar', 'neighborhood:id,name_ar'])
            ->latest('number')
            ->paginate(25);

        return view('service_contracts.index', compact('contracts'));
    }

    public function create(Request $request): View
    {
        return view('service_contracts.create', [
            'cities' => GeoCity::query()->orderBy('name_ar')->pluck('name_ar', 'id'),
            'kinds' => ValuationFieldGrid::kinds(),
            'representative' => $request->user()->name,
        ]);
    }

    public function store(StoreServiceContractRequest $request, CreateServiceContractAction $action): RedirectResponse
    {
        $contract = $action->execute($request->user(), $request->validated());

        return redirect()
            ->route('dashboard.service-contracts.index')
            ->with('success', __('Contract :number created.', ['number' => $contract->number]))
            ->with('download', route('dashboard.service-contracts.pdf', $contract));
    }

    public function pdf(
        Request $request,
        ServiceContract $serviceContract,
        ArabicAmountInWords $words,
        RenderDocumentPdfAction $render,
    ): Response {
        $serviceContract->loadMissing(['company', 'city', 'neighborhood']);

        return $render->execute('service_contracts.pdf', [
            'contract' => $serviceContract,
            'company' => $serviceContract->company,
            'firstParty' => $serviceContract->company?->name ?: config('app.name'),
            'contractValue' => number_format((float) $serviceContract->contract_value, 2),
            'date' => HijriDate::fromGregorian($serviceContract->contract_date),
            'instrumentDate' => $serviceContract->instrument_date ? HijriDate::fromGregorian($serviceContract->instrument_date) : null,
            'valueInWords' => $words->convert((float) $serviceContract->contract_value),
        ], 'contract-'.$serviceContract->number.'.pdf', $request->boolean('download'));
    }
}
