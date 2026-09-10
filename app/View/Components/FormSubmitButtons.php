<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class FormSubmitButtons extends Component
{
    public $cancelroute;

    public $screenname;

    public $action;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($screenname, $cancelroute)
    {
        if (strpos(Route::currentRouteName(), 'create') !== false) {
            $this->action = 'create';
        }
        if (strpos(Route::currentRouteName(), 'edit') !== false) {
            $this->action = 'edit';
        }
        $this->cancelroute = $cancelroute;
        $this->screenname = $screenname;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render()
    {
        return view('components.form-submit-buttons');
    }
}
