<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Overrides\Spatie\Role;

class UpdateRoleRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $role = $this->route('role');

        return $role instanceof Role
            && ($this->user()?->can('update', $role) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Role::$rules;
        
        return $rules;
    }
}
