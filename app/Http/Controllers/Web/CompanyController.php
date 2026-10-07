<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Company\UpdateCompanyDefaultSharesAction;
use App\Actions\Company\UpdateCompanyProfileAction;
use App\Actions\Company\UploadCompanyBrandingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateCompanyProfileRequest;
use App\Http\Requests\Company\UpdateCompanySharesRequest;
use App\Http\Requests\Company\UploadCompanyBrandingRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class CompanyController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $this->authorize('viewAny', Company::class);

        $company = Company::query()->orderBy('id')->first();

        if ($company !== null) {
            return redirect()->route('dashboard.companies.edit', $company);
        }

        $companies = Company::query()->latest('id')->paginate(25);

        return view('companies.index', compact('companies'));
    }

    public function edit(Company $company): View
    {
        $this->authorize('view', $company);

        return view('companies.edit', compact('company'));
    }

    public function updateProfile(
        UpdateCompanyProfileRequest $request,
        Company $company,
        UpdateCompanyProfileAction $action
    ): RedirectResponse {
        $action($company, $request->validated());

        Flash::success(__('messages.updated', ['model' => __('models/companies.singular')]));

        return redirect()->route('dashboard.companies.edit', $company);
    }

    public function updateShares(
        UpdateCompanySharesRequest $request,
        Company $company,
        UpdateCompanyDefaultSharesAction $action
    ): RedirectResponse {
        $action($company, $request->validated());

        Flash::success(__('Company shares updated'));

        return redirect()->route('dashboard.companies.edit', $company);
    }

    public function uploadBranding(
        UploadCompanyBrandingRequest $request,
        Company $company,
        UploadCompanyBrandingAction $action
    ): RedirectResponse {
        $action($company, (string) $request->validated('type'), $request->file('image'));

        Flash::success(__('Company branding updated'));

        return redirect()->route('dashboard.companies.edit', $company);
    }
}
