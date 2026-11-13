<?php

declare(strict_types=1);

use App\Models\SystemComponent;
use App\Services\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menus render icon_name through Feather, but these rows held Bootstrap Icons / FontAwesome
     * names. feather.replace() throws on the first unknown name and stops rendering every icon after it.
     *
     * @return array<string, string> old icon_name => Feather icon_name
     */
    private static function map(): array
    {
        return [
            'gear' => 'settings',
            'people' => 'users',
            'file-earmark-text' => 'file-text',
            'journal-check' => 'check-square',
            'cash-stack' => 'dollar-sign',
            'geo-alt' => 'map-pin',
            'pin-map' => 'navigation',
            'building' => 'layers',
            'file-signature' => 'edit-3',
        ];
    }

    public function up(): void
    {
        $this->rename(self::map());
    }

    public function down(): void
    {
        // Only icon names change, no data is lost; 'users' is left alone because it was already valid on other rows.
        $this->rename(array_diff_key(array_flip(self::map()), ['users' => true]));
    }

    /**
     * @param  array<string, string>  $map
     */
    private function rename(array $map): void
    {
        if (! Schema::hasTable('system_components')) {
            return;
        }

        foreach ($map as $from => $to) {
            SystemComponent::query()->withTrashed()->where('icon_name', $from)->update(['icon_name' => $to]);
        }

        app(MenuService::class)->clearAllMenuCache();
    }
};
