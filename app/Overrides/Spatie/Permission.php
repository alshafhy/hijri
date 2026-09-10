<?php

namespace App\Overrides\Spatie;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $guarded = [];

    public $fillable = [
        'name',
        'guard_name',
        'system_component_id',
    ];
}
