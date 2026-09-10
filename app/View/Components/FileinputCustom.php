<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FileinputCustom extends Component
{
    public $name;

    public $labelTitle;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $labelTitle = '')
    {
        $this->name = $name;
        $this->labelTitle = $labelTitle;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render()
    {
        return view('components.fileinput-custom');
    }
}
