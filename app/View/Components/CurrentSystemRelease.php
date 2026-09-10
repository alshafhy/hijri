<?php

namespace App\View\Components;

use App\Models\SystemRelease;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CurrentSystemRelease extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $currentSystemRelease = SystemRelease::query()->latest('id')->value('version_number') ?? '';

        return view('components.current-system-release', compact('currentSystemRelease'));
    }
}
