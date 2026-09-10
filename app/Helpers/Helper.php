<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

class Helper
{
    /**
     * @return array<string, mixed>
     */
    public static function applClasses(): array
    {
        $defaultData = [
            'mainLayoutType' => 'vertical',
            'theme' => 'light',
            'sidebarCollapsed' => false,
            'navbarColor' => '',
            'horizontalMenuType' => 'floating',
            'verticalMenuNavbarType' => 'floating',
            'footerType' => 'static',
            'layoutWidth' => 'boxed',
            'showMenu' => true,
            'bodyClass' => '',
            'pageClass' => '',
            'pageHeader' => true,
            'contentLayout' => 'default',
            'blankPage' => false,
            'defaultLanguage' => 'ar',
            'direction' => env('APP_DIRECTION', 'rtl'),
        ];

        $data = array_merge($defaultData, config('custom.custom', []));

        $allOptions = [
            'mainLayoutType' => ['vertical', 'horizontal'],
            'theme' => [
                'light' => 'light',
                'dark' => 'dark-layout',
                'bordered' => 'bordered-layout',
                'semi-dark' => 'semi-dark-layout',
            ],
            'sidebarCollapsed' => [true, false],
            'showMenu' => [true, false],
            'layoutWidth' => ['full', 'boxed'],
            'navbarColor' => [
                'bg-primary',
                'bg-info',
                'bg-warning',
                'bg-success',
                'bg-danger',
                'bg-dark',
            ],
            'horizontalMenuType' => [
                'floating' => 'navbar-floating',
                'static' => 'navbar-static',
                'sticky' => 'navbar-sticky',
            ],
            'horizontalMenuClass' => [
                'static' => '',
                'sticky' => 'fixed-top',
                'floating' => 'floating-nav',
            ],
            'verticalMenuNavbarType' => [
                'floating' => 'navbar-floating',
                'static' => 'navbar-static',
                'sticky' => 'navbar-sticky',
                'hidden' => 'navbar-hidden',
            ],
            'navbarClass' => [
                'floating' => 'floating-nav',
                'static' => 'navbar-static-top',
                'sticky' => 'fixed-top',
                'hidden' => 'd-none',
            ],
            'footerType' => [
                'static' => 'footer-static',
                'sticky' => 'footer-fixed',
                'hidden' => 'footer-hidden',
            ],
            'pageHeader' => [true, false],
            'contentLayout' => [
                'default',
                'content-left-sidebar',
                'content-right-sidebar',
                'content-detached-left-sidebar',
                'content-detached-right-sidebar',
            ],
            'blankPage' => [false, true],
            'sidebarPositionClass' => [
                'content-left-sidebar' => 'sidebar-left',
                'content-right-sidebar' => 'sidebar-right',
                'content-detached-left-sidebar' => 'sidebar-detached sidebar-left',
                'content-detached-right-sidebar' => 'sidebar-detached sidebar-right',
                'default' => 'default-sidebar-position',
            ],
            'contentsidebarClass' => [
                'content-left-sidebar' => 'content-right',
                'content-right-sidebar' => 'content-left',
                'content-detached-left-sidebar' => 'content-detached content-right',
                'content-detached-right-sidebar' => 'content-detached content-left',
                'default' => 'default-sidebar',
            ],
            'defaultLanguage' => [
                'ar' => 'ar',
                'en' => 'en',
            ],
            'direction' => ['ltr', 'rtl'],
        ];

        foreach ($allOptions as $key => $value) {
            if (! array_key_exists($key, $defaultData)) {
                continue;
            }

            if (gettype($defaultData[$key]) !== gettype($data[$key])) {
                $data[$key] = $defaultData[$key];

                continue;
            }

            if (! is_string($data[$key])) {
                continue;
            }

            if ($data[$key] === '') {
                $data[$key] = $defaultData[$key];

                continue;
            }

            if (! array_key_exists($data[$key], $value)) {
                $result = array_search($data[$key], $value, true);
                if ($result === false) {
                    $data[$key] = $defaultData[$key];
                }
            }
        }

        $layoutClasses = [
            'theme' => $data['theme'],
            'layoutTheme' => $allOptions['theme'][$data['theme']],
            'sidebarCollapsed' => $data['sidebarCollapsed'],
            'showMenu' => $data['showMenu'],
            'layoutWidth' => $data['layoutWidth'],
            'verticalMenuNavbarType' => $allOptions['verticalMenuNavbarType'][$data['verticalMenuNavbarType']],
            'navbarClass' => $allOptions['navbarClass'][$data['verticalMenuNavbarType']],
            'navbarColor' => $data['navbarColor'],
            'horizontalMenuType' => $allOptions['horizontalMenuType'][$data['horizontalMenuType']],
            'horizontalMenuClass' => $allOptions['horizontalMenuClass'][$data['horizontalMenuType']],
            'footerType' => $allOptions['footerType'][$data['footerType']],
            'sidebarClass' => '',
            'bodyClass' => $data['bodyClass'],
            'pageClass' => $data['pageClass'],
            'pageHeader' => $data['pageHeader'],
            'blankPage' => $data['blankPage'],
            'blankPageClass' => '',
            'contentLayout' => $data['contentLayout'],
            'sidebarPositionClass' => $allOptions['sidebarPositionClass'][$data['contentLayout']],
            'contentsidebarClass' => $allOptions['contentsidebarClass'][$data['contentLayout']],
            'mainLayoutType' => $data['mainLayoutType'],
            'defaultLanguage' => $allOptions['defaultLanguage'][$data['defaultLanguage']],
            'direction' => session('direction', $data['direction']),
        ];

        if (! session()->has('locale')) {
            app()->setLocale($layoutClasses['defaultLanguage']);
        }

        if ($layoutClasses['sidebarCollapsed'] === true || $layoutClasses['sidebarCollapsed'] === 'true') {
            $layoutClasses['sidebarClass'] = 'menu-collapsed';
        }

        if ($layoutClasses['blankPage'] === true || $layoutClasses['blankPage'] === 'true') {
            $layoutClasses['blankPageClass'] = 'blank-page';
        }

        return $layoutClasses;
    }

    public static function updatePageConfig(?array $pageConfigs): void
    {
        $demo = 'custom';

        if (isset($pageConfigs) && count($pageConfigs) > 0) {
            foreach ($pageConfigs as $config => $val) {
                Config::set('custom.'.$demo.'.'.$config, $val);
            }
        }
    }

    public static function checkCurrentRouteName(?string $routeName = null): bool
    {
        $current = (string) Route::currentRouteName();

        if ($routeName === null || $routeName === '') {
            return false;
        }

        if (str_contains($current, $routeName.'.')) {
            return true;
        }

        return str_contains($current, $routeName);
    }

    /**
     * @param  array<int, int|User>|Collection<int, User>|User|int  $ids
     */
    public static function SendNotifications(
        string $title,
        string $message,
        array|Collection|User|int $ids,
        string $type = 'User',
        ?string $link = null,
        string $classBg = 'bg-light-success',
        string $classIcon = 'check',
    ): void {
        if ($type !== 'User') {
            return;
        }

        if ($ids instanceof User) {
            $notifiables = collect([$ids]);
        } elseif ($ids instanceof Collection) {
            $notifiables = $ids;
        } else {
            $idList = is_array($ids) ? $ids : [$ids];
            $notifiables = User::query()->whereIn('id', $idList)->get();
        }

        Notification::send($notifiables, new GeneralNotification($title, $message, $link, $classBg, $classIcon));
    }

    public static function dateFormat(mixed $value, string $format = 'Y-m-d'): string
    {
        return Carbon::parse($value)->format($format);
    }

    public static function formatBytes(int|float $size, int $precision = 2): string
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = [' bytes', ' KB', ' MB', ' GB', ' TB'];

            return round(pow(1024, $base - floor($base)), $precision).$suffixes[(int) floor($base)];
        }

        return (string) $size;
    }

    public static function checkActiveRoute(string $menuRouteName, string|int $compType = ''): bool
    {
        if ($compType && (int) $compType === 4) {
            $routeParameters = Route::current()?->parameters() ?? [];
            $routeReportName = $routeParameters['reportName'] ?? '';

            return $routeReportName == $menuRouteName;
        }

        $menuRouteArr = [
            $menuRouteName.'.index',
            $menuRouteName.'.edit',
            $menuRouteName.'.show',
            $menuRouteName.'.create',
            $menuRouteName,
        ];

        return in_array(Route::currentRouteName(), $menuRouteArr, true);
    }

    public static function redirectAfterSaving(int|string $id, object $request, string $routeName): RedirectResponse
    {
        if (isset($request->redirectAction) && $request->redirectAction === 'edit') {
            return redirect(route($routeName.'.edit', $id));
        }

        return redirect(route($routeName.'.index'));
    }

    /**
     * @return Collection<int|string, mixed>
     */
    public static function getConfigOptionsList(string $constName): Collection
    {
        return collect(config($constName))
            ->map(fn ($item) => $item['title'] ?? $item)
            ->prepend(__('messages.none'), 0);
    }

    public static function pdfImagePath(string $imagePath): string
    {
        if (env('APP_ENV') === 'production') {
            return url($imagePath);
        }

        return public_path($imagePath);
    }
}
