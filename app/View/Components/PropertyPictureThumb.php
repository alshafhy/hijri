<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\PropertyPicture;
use App\Support\Valuation\PropertyPictureMedia;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PropertyPictureThumb extends Component
{
    /** @var array{src: string, available: bool, label: string} */
    public array $display;

    public function __construct(
        public PropertyPicture $picture,
        ?PropertyPictureMedia $media = null,
    ) {
        $this->display = ($media ?? new PropertyPictureMedia)->display($picture);
    }

    public function render(): View|Closure|string
    {
        return view('components.property-picture-thumb');
    }
}
