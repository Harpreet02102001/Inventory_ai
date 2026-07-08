<?php
// app/Http/Requests/User/UpdateUserRequest.php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateUserRequest
 *
 * Password is 'nullable' here (unlike 'required' on Store) — this is
 * what makes "leave blank to keep current password" possible. When
 * present, it's still validated the same way (min 8, confirmed).
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'users.edit' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
