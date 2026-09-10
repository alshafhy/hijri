<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;
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
            'direction' => env('APP_DIRECTION', (env('MIX_CONTENT_DIRECTION') ?: 'rtl')),
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
                'sticky' => 'fixed-footer',
                'hidden' => 'footer-hidden',
            ],
            'pageHeader' => [true, false],
            'contentLayout' => [
                'default' => 'default',
                'detached' => 'detached',
            ],
            'blankPage' => [true, false],
            'sidebarPositionClass' => [
                'vertical' => 'main-menu-content',
                'horizontal' => '',
            ],
            'contentsidebarClass' => [
                'default' => 'default-sidebar',
                'detached' => 'detached-sidebar',
            ],
            'defaultLanguage' => [
                'en' => 'en',
                'ar' => 'ar',
            ],
            'direction' => ['ltr', 'rtl'],
        ];

        foreach ($allOptions as $key => $value) {
            if (array_key_exists($key, $defaultData) && isset($data[$key]) && array_key_exists($data[$key], $value)) {
                $result = $value[$data[$key]];
            } elseif (array_key_exists($key, $defaultData) && isset($data[$key]) && is_array($value) && in_array($data[$key], $value, true)) {
                $result = $data[$key];
            } else {
                $result = array_values($value)[0];
            }

            $data[$key] = $result;
        }

        $themeName = $data['theme'];
        $data['theme'] = $themeName;
        $data['themeName'] = $themeName;

        return $data;
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

    public static function redirectAfterSaving(int|string $id, object $request, string $routeName): \Illuminate\Http\RedirectResponse
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
