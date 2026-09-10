<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormToolbar extends Component
{
    public string $indexRoute;

    public string $createRoute;

    public string $showRoute;

    public string $editRoute;

    public string $destroyRoute;

    public ?string $createPermission;

    public ?string $editPermission;

    public ?string $viewPermission;

    public ?string $deletePermission;

    public function __construct(
        public string $actionname,
        public string $screenname,
        public string|int $key = '',
    ) {
        $this->indexRoute = 'dashboard.'.$screenname.'.index';
        $this->createRoute = 'dashboard.'.$screenname.'.create';
        $this->showRoute = 'dashboard.'.$screenname.'.show';
        $this->editRoute = 'dashboard.'.$screenname.'.edit';
        $this->destroyRoute = 'dashboard.'.$screenname.'.destroy';

        $this->createPermission = match ($screenname) {
            'users' => 'user.create',
            'roles' => 'role.create',
            default => null,
        };
        $this->editPermission = match ($screenname) {
            'users' => 'user.edit',
            'roles' => 'role.edit',
            default => null,
        };
        $this->viewPermission = match ($screenname) {
            'users' => 'user.view',
            'roles' => 'role.view',
            default => null,
        };
        $this->deletePermission = match ($screenname) {
            'users' => 'user.delete',
            'roles' => 'role.delete',
            default => null,
        };
    }

    public function render(): View
    {
        return view('components.form-toolbar');
    }
}
