<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CheckBox extends Component
{
    public $name;

    public $labelTitle;

    public $isCheckedByDefault;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $labelTitle = '', $isCheckedByDefault = null)
    {
        $this->name = $name;
        $this->labelTitle = $labelTitle;
        $this->isCheckedByDefault = $isCheckedByDefault;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render()
    {
        return view('components.check-box');
    }
}
