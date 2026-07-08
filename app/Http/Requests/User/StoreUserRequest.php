<?php
// app/Http/Requests/User/StoreUserRequest.php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreUserRequest
 *
 * Validates creation of a new user account, including required
 * password confirmation and at least one role assignment.
 */
class StoreUserRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'users.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            // 'confirmed' requires a matching password_confirmation field in
            // the form — Laravel checks this automatically, no manual code needed.
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status'   => ['required', Rule::in(['active', 'inactive'])],

            'roles'   => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique'   => 'A user with this email already exists.',
            'password.min'   => 'Password must be at least 8 characters.',
            'roles.required' => 'Assign at least one role to this user.',
        ];
    }
}
