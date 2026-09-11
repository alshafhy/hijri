<?php

declare(strict_types=1);

use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\ContractController;
use App\Http\Controllers\Web\ContractorController;
use App\Http\Controllers\Web\FinancialController;
use App\Http\Controllers\Web\GeoCityController;
use App\Http\Controllers\Web\GeoNeighborhoodController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LanguageController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OfferController;
use App\Http\Controllers\Web\PartnerController;
use App\Http\Controllers\Web\PropertyPictureController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ValuationReportExportController;
use App\Http\Controllers\Web\ValuationRequestController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('locale/{locale}', [LocaleController::class, 'switch'])
    ->name('locale.switch')
    ->where('locale', 'ar|en');

Route::get('/underMaintenance', [HomeController::class, 'underMaintenance'])->name('mm');
Route::get('/pageComingSoon', [HomeController::class, 'pageComingSoon'])->name('pageComingSoon');
Route::get('/', [HomeController::class, 'pageComingSoon'])->name('home-page');

Auth::routes();

Route::middleware('auth')->group(function (): void {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('lang/{locale}', [LanguageController::class, 'swap']);

    Route::get('me/notifications/read', [NotificationController::class, 'markAsReadNotificationAll'])
        ->name('markAsReadNotificationAll');
    Route::get('me/notifications/read/{id}', [NotificationController::class, 'markAsReadNotification'])
        ->name('markAsReadNotification');
    Route::get('me/notifications/', [NotificationController::class, 'showNotification'])
        ->name('showNotification');

    Route::prefix('dashboard')->name('dashboard.')->group(function (): void {
        Route::get('users/change-password', [UserController::class, 'changePassword'])
            ->name('users.change-password');
        Route::match(['put', 'post'], 'users/change-password', [UserController::class, 'updatePassword'])
            ->name('users.update-password');

        Route::resource('users', UserController::class)
            ->middleware('permission:user.view');
        Route::resource('roles', RoleController::class)
            ->middleware('permission:role.view');
        Route::get('roles/{role}/permissions/{objectId?}', [RoleController::class, 'getPermissionsView'])
            ->name('roles.permissions')
            ->middleware('permission:role.edit');

        Route::get('valuation-requests', [ValuationRequestController::class, 'index'])
            ->name('valuation-requests.index')
            ->middleware('permission:valuation_request.view');
        Route::get('valuation-requests/create', [ValuationRequestController::class, 'create'])
            ->name('valuation-requests.create')
            ->middleware('permission:valuation_request.create');
        Route::post('valuation-requests', [ValuationRequestController::class, 'store'])
            ->name('valuation-requests.store')
            ->middleware('permission:valuation_request.create');
        Route::get('valuation-requests/quick-search', [ValuationRequestController::class, 'quickSearch'])
            ->name('valuation-requests.quick-search')
            ->middleware('permission:valuation_request.view');
        Route::get('valuation-requests/advanced-search', [ValuationRequestController::class, 'advancedSearchForm'])
            ->name('valuation-requests.advanced-search')
            ->middleware('permission:valuation_request.advanced_search');
        Route::get('valuation-requests/advanced-search/results', [ValuationRequestController::class, 'advancedSearch'])
            ->name('valuation-requests.advanced-search.results')
            ->middleware('permission:valuation_request.advanced_search');
        Route::get('valuation-requests/deleted', [ValuationRequestController::class, 'deleted'])
            ->name('valuation-requests.deleted')
            ->middleware('permission:valuation_request.view_deleted');
        Route::post('valuation-requests/{valuationRequest}/restore', [ValuationRequestController::class, 'restore'])
            ->name('valuation-requests.restore')
            ->middleware('permission:valuation_request.view_deleted')
            ->whereNumber('valuationRequest');
        Route::get('valuation-requests/qima-pending', [ValuationRequestController::class, 'qimaPending'])
            ->name('valuation-requests.qima-pending')
            ->middleware('permission:valuation_request.qima_upload');
        Route::get('valuation-requests/activity-logs', [ValuationRequestController::class, 'activityLogs'])
            ->name('valuation-requests.activity-logs')
            ->middleware('permission:valuation_request.view_logs');

        Route::get('valuation-requests/{valuationRequest}', [ValuationRequestController::class, 'show'])
            ->name('valuation-requests.show')
            ->middleware('permission:valuation_request.view');
        Route::get('valuation-requests/{valuationRequest}/edit', [ValuationRequestController::class, 'edit'])
            ->name('valuation-requests.edit')
            ->middleware('permission:valuation_request.edit');
        Route::put('valuation-requests/{valuationRequest}', [ValuationRequestController::class, 'update'])
            ->name('valuation-requests.update')
            ->middleware('permission:valuation_request.edit');
        Route::get('valuation-requests/{valuationRequest}/edit-info', [ValuationRequestController::class, 'editInfo'])
            ->name('valuation-requests.edit-info')
            ->middleware('permission:valuation_request.edit');
        Route::put('valuation-requests/{valuationRequest}/edit-info', [ValuationRequestController::class, 'updateInfo'])
            ->name('valuation-requests.update-info')
            ->middleware('permission:valuation_request.edit');
        Route::post('valuation-requests/{valuationRequest}/send', [ValuationRequestController::class, 'send'])
            ->name('valuation-requests.send')
            ->middleware('permission:valuation_request.send');
        Route::post('valuation-requests/{valuationRequest}/under-evaluation', [ValuationRequestController::class, 'underEvaluation'])
            ->name('valuation-requests.under-evaluation')
            ->middleware('permission:valuation_request.send');
        Route::post('valuation-requests/{valuationRequest}/mark-evaluated', [ValuationRequestController::class, 'markEvaluated'])
            ->name('valuation-requests.mark-evaluated')
            ->middleware('permission:valuation_request.mark_evaluated');
        Route::post('valuation-requests/{valuationRequest}/approve', [ValuationRequestController::class, 'approve'])
            ->name('valuation-requests.approve')
            ->middleware('permission:valuation_request.approve_final');
        Route::post('valuation-requests/{valuationRequest}/unapprove', [ValuationRequestController::class, 'unapprove'])
            ->name('valuation-requests.unapprove')
            ->middleware('permission:valuation_request.unapprove');
        Route::post('valuation-requests/{valuationRequest}/reject', [ValuationRequestController::class, 'reject'])
            ->name('valuation-requests.reject')
            ->middleware('permission:valuation_request.reject');
        Route::post('valuation-requests/{valuationRequest}/cancel', [ValuationRequestController::class, 'cancel'])
            ->name('valuation-requests.cancel')
            ->middleware('permission:valuation_request.cancel');
        Route::post('valuation-requests/{valuationRequest}/duplicate', [ValuationRequestController::class, 'duplicate'])
            ->name('valuation-requests.duplicate')
            ->middleware('permission:valuation_request.duplicate');
        Route::get('valuation-requests/{valuationRequest}/attachments-zip', [ValuationRequestController::class, 'downloadAttachmentsZip'])
            ->name('valuation-requests.attachments-zip')
            ->middleware('permission:valuation_request.view');
        Route::delete('valuation-requests/{valuationRequest}/pictures/{propertyPicture}', [ValuationRequestController::class, 'destroyPicture'])
            ->name('valuation-requests.pictures.destroy')
            ->middleware('permission:valuation_request.edit');
        Route::put('valuation-requests/{valuationRequest}/fee-shares', [ValuationRequestController::class, 'updateFeeShares'])
            ->name('valuation-requests.fee-shares')
            ->middleware('permission:valuation_request.manage_fee_shares');
        Route::get('valuation-requests/{valuationRequest}/barcode', [ValuationRequestController::class, 'barcode'])
            ->name('valuation-requests.barcode')
            ->middleware('permission:valuation_request.view');
        Route::post('valuation-requests/{valuationRequest}/qima-toggle', [ValuationRequestController::class, 'toggleQima'])
            ->name('valuation-requests.qima-toggle')
            ->middleware('permission:valuation_request.qima_upload');
        Route::post('valuation-requests/{valuationRequest}/change-evaluator', [ValuationRequestController::class, 'changeEvaluator'])
            ->name('valuation-requests.change-evaluator')
            ->middleware('permission:valuation_request.change_evaluator');
        Route::post('valuation-requests/{valuationRequest}/change-coordinator', [ValuationRequestController::class, 'changeCoordinator'])
            ->name('valuation-requests.change-coordinator')
            ->middleware('permission:valuation_request.change_coordinator');
        Route::post('valuation-requests/{valuationRequest}/change-property-type', [ValuationRequestController::class, 'changePropertyType'])
            ->name('valuation-requests.change-property-type')
            ->middleware('permission:valuation_request.change_property_type');
        Route::post('valuation-requests/{valuationRequest}/override-amount', [ValuationRequestController::class, 'overrideAmount'])
            ->name('valuation-requests.override-amount')
            ->middleware('permission:valuation_request.override_amount');
        Route::post('valuation-requests/{valuationRequest}/official-report', [ValuationRequestController::class, 'uploadOfficialReport'])
            ->name('valuation-requests.official-report')
            ->middleware('permission:valuation_request.qima_upload');

        Route::get('valuation-requests/{valuationRequest}/exports/{variant}', [ValuationReportExportController::class, 'queue'])
            ->name('valuation-requests.exports.queue')
            ->whereIn('variant', ['enforcement', 'full', 'full-draft'])
            ->middleware('permission:valuation_request.export_pdf');
        Route::get('valuation-requests/{valuationRequest}/exports/{variant}/{key}/status', [ValuationReportExportController::class, 'status'])
            ->name('valuation-requests.exports.status')
            ->whereIn('variant', ['enforcement', 'full', 'full-draft'])
            ->middleware('permission:valuation_request.export_pdf');
        Route::get('valuation-requests/{valuationRequest}/exports/{variant}/{key}/download', [ValuationReportExportController::class, 'download'])
            ->name('valuation-requests.exports.download')
            ->whereIn('variant', ['enforcement', 'full', 'full-draft'])
            ->middleware('permission:valuation_request.export_pdf');

        Route::get('property-pictures/{propertyPicture}/file', [PropertyPictureController::class, 'file'])
            ->name('property-pictures.file')
            ->middleware('permission:valuation_request.view');

        // Partners
        Route::middleware('permission:partner.view')->group(function (): void {
            Route::get('partners', [PartnerController::class, 'index'])->name('partners.index');
            Route::get('partners/create', [PartnerController::class, 'create'])->name('partners.create')->middleware('permission:partner.create');
            Route::post('partners', [PartnerController::class, 'store'])->name('partners.store')->middleware('permission:partner.create');
            Route::get('partners/{partner}', [PartnerController::class, 'show'])->name('partners.show');
            Route::get('partners/{partner}/edit', [PartnerController::class, 'edit'])->name('partners.edit')->middleware('permission:partner.edit');
            Route::put('partners/{partner}', [PartnerController::class, 'update'])->name('partners.update')->middleware('permission:partner.edit');
            Route::delete('partners/{partner}', [PartnerController::class, 'destroy'])->name('partners.destroy')->middleware('permission:partner.delete');
            Route::post('partners/{partner}/activate', [PartnerController::class, 'activate'])->name('partners.activate')->middleware('permission:partner.activate');
            Route::post('partners/{partner}/deactivate', [PartnerController::class, 'deactivate'])->name('partners.deactivate')->middleware('permission:partner.activate');
            Route::post('partners/{partner}/contacts/{contact}/deactivate', [PartnerController::class, 'deactivateContact'])->name('partners.contacts.deactivate')->middleware('permission:partner.edit');
        });

        // Contractors
        Route::middleware('permission:contractor.view')->group(function (): void {
            Route::get('contractors', [ContractorController::class, 'index'])->name('contractors.index');
            Route::get('contractors/create', [ContractorController::class, 'create'])->name('contractors.create')->middleware('permission:contractor.create');
            Route::post('contractors', [ContractorController::class, 'store'])->name('contractors.store')->middleware('permission:contractor.create');
            Route::get('contractors/{contractor}', [ContractorController::class, 'show'])->name('contractors.show');
            Route::get('contractors/{contractor}/edit', [ContractorController::class, 'edit'])->name('contractors.edit')->middleware('permission:contractor.edit');
            Route::put('contractors/{contractor}', [ContractorController::class, 'update'])->name('contractors.update')->middleware('permission:contractor.edit');
            Route::delete('contractors/{contractor}', [ContractorController::class, 'destroy'])->name('contractors.destroy')->middleware('permission:contractor.delete');
            Route::post('contractors/{contractor}/activate', [ContractorController::class, 'activate'])->name('contractors.activate')->middleware('permission:contractor.activate');
            Route::post('contractors/{contractor}/deactivate', [ContractorController::class, 'deactivate'])->name('contractors.deactivate')->middleware('permission:contractor.activate');
            Route::post('contractors/{contractor}/contacts/{contact}/deactivate', [ContractorController::class, 'deactivateContact'])->name('contractors.contacts.deactivate')->middleware('permission:contractor.edit');
        });

        // Contracts
        Route::middleware('permission:contract.view')->group(function (): void {
            Route::get('contracts', [ContractController::class, 'index'])->name('contracts.index');
            Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
            Route::post('contracts/{contract}/mark-paid', [ContractController::class, 'markPaid'])->name('contracts.mark-paid')->middleware('permission:contract.mark_paid');
        });

        // Offers
        Route::middleware('permission:offer.view')->group(function (): void {
            Route::get('offers', [OfferController::class, 'index'])->name('offers.index');
            Route::get('offers/create', [OfferController::class, 'create'])->name('offers.create')->middleware('permission:offer.create');
            Route::post('offers', [OfferController::class, 'store'])->name('offers.store')->middleware('permission:offer.create');
            Route::get('offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
            Route::get('offers/{offer}/edit', [OfferController::class, 'edit'])->name('offers.edit')->middleware('permission:offer.edit');
            Route::put('offers/{offer}', [OfferController::class, 'update'])->name('offers.update')->middleware('permission:offer.edit');
            Route::post('offers/{offer}/activate', [OfferController::class, 'activate'])->name('offers.activate')->middleware('permission:offer.activate');
            Route::post('offers/{offer}/deactivate', [OfferController::class, 'deactivate'])->name('offers.deactivate')->middleware('permission:offer.deactivate');
            Route::post('offers/{offer}/estates/{estate}/mark-paid', [OfferController::class, 'markEstatePaid'])->name('offers.estates.mark-paid')->middleware('permission:offer.edit');
            Route::post('offers/{offer}/estates/{estate}/activate', [OfferController::class, 'activateEstate'])->name('offers.estates.activate')->middleware('permission:offer.edit');
            Route::post('offers/{offer}/estates/{estate}/deactivate', [OfferController::class, 'deactivateEstate'])->name('offers.estates.deactivate')->middleware('permission:offer.edit');
        });

        // Geo cities & neighborhoods
        Route::middleware('permission:geo_city.view')->group(function (): void {
            Route::get('geo-cities', [GeoCityController::class, 'index'])->name('geo-cities.index');
            Route::get('geo-cities/create', [GeoCityController::class, 'create'])->name('geo-cities.create')->middleware('permission:geo_city.create');
            Route::post('geo-cities', [GeoCityController::class, 'store'])->name('geo-cities.store')->middleware('permission:geo_city.create');
            Route::get('geo-cities/{geoCity}', [GeoCityController::class, 'show'])->name('geo-cities.show');
            Route::delete('geo-cities/{geoCity}', [GeoCityController::class, 'destroy'])->name('geo-cities.destroy')->middleware('permission:geo_city.delete');
            Route::post('geo-neighborhoods', [GeoNeighborhoodController::class, 'store'])->name('geo-neighborhoods.store')->middleware('permission:geo_neighborhood.create');
            Route::delete('geo-neighborhoods/{geoNeighborhood}', [GeoNeighborhoodController::class, 'destroy'])->name('geo-neighborhoods.destroy')->middleware('permission:geo_neighborhood.delete');
        });

        // Financial board
        Route::get('financial', [FinancialController::class, 'index'])
            ->name('financial.index')
            ->middleware('permission:financial.view');

        // Company profile
        Route::middleware('permission:company.view')->group(function (): void {
            Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
            Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
            Route::put('companies/{company}/profile', [CompanyController::class, 'updateProfile'])->name('companies.profile')->middleware('permission:company.edit');
            Route::put('companies/{company}/shares', [CompanyController::class, 'updateShares'])->name('companies.shares')->middleware('permission:company.edit');
            Route::post('companies/{company}/branding', [CompanyController::class, 'uploadBranding'])->name('companies.branding')->middleware('permission:company.edit');
        });
    });
});
