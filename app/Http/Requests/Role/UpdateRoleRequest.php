<?php
// app/Http/Requests/Role/UpdateRoleRequest.php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateRoleRequest
 *
 * Same rules as creation, with uniqueness ignoring the role being edited.
 */
class UpdateRoleRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'roles.edit' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z_]+$/',
                Rule::unique('roles', 'name')->ignore($this->route('role')),
            ],
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
            'name.regex'  => 'The machine name may only contain lowercase letters and underscores.',
        ];
    }
}
