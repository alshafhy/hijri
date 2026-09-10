<?php

declare(strict_types=1);

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LanguageController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PropertyPictureController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\UserController;
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
        Route::get('valuation-requests/{valuationRequest}', [ValuationRequestController::class, 'show'])
            ->name('valuation-requests.show')
            ->middleware('permission:valuation_request.view');
        Route::post('valuation-requests/{valuationRequest}/official-report', [ValuationRequestController::class, 'uploadOfficialReport'])
            ->name('valuation-requests.official-report')
            ->middleware('permission:valuation_request.qima_upload');
        Route::get('property-pictures/{propertyPicture}/file', [PropertyPictureController::class, 'file'])
            ->name('property-pictures.file')
            ->middleware('permission:valuation_request.view');
    });
});
