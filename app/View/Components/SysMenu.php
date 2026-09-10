<?php

namespace App\View\Components;

use App\Models\SystemComponent;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SysMenu extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render()
    {
        // $nodes  = SystemComponent::get()->toTree();
        $nodes = $this->getNodesByPermissions();

        return view('components.sys-menu')->with('nodes', $nodes);
    }

    private function getNodesByPermissions()
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }
        // if(session('current_user_menu')){
        //     $nodes  =session('current_user_menu');
        // }else{
        $nodes = SystemComponent::get()->toTree();
        session(['current_user_menu' => $nodes]);
        // }

        if ($user->hasRole('admin')) {
            return $nodes;
        }
        $permissions = $user->getAllPermissions();
        // dd($permissions);
        $permissions = $permissions->pluck('name');

        foreach ($nodes as $node) {
            $filtered = $node->children->reject(function ($value, $key) use ($permissions) {
                $route_name = $value->route_name;
                $prefix = $value->prefix;
                // if($prefix=='commonScreens'){
                //     $prefix='cmn';
                // }
                if ($value->comp_type == 4) {
                    $p = $prefix.'.'.$route_name;
                } else {
                    $p = $prefix.'.'.$route_name.'.'.'index';
                }

                // dd(!$permissions);
                return ! $permissions->contains($p);
            });
            $node->setRelation('children', $filtered);
        }
        $filtered = $nodes->reject(function ($value, $key) {
            if ($value->children->all()) {
                return false;
            } else {
                return true;
            }
        });

        return $filtered;
    }
}
