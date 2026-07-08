<?php
// app/Http/Requests/Role/StoreRoleRequest.php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreRoleRequest
 *
 * Validates creation of a new role, including the array of permission
 * IDs to grant it. 'permissions' is allowed to be an empty array (a
 * role with zero permissions is valid, if unusual) but must be an
 * array if present, and every ID in it must genuinely exist.
 */
class StoreRoleRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'roles.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100', 'unique:roles,name', 'regex:/^[a-z_]+$/'],
            'display_name' => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:500'],

            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this machine name already exists.',
            'name.regex'  => 'The machine name may only contain lowercase letters and underscores (e.g. "sales_person").',
        ];
    }
}
