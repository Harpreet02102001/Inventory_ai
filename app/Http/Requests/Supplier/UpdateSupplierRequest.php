<?php

declare(strict_types=1);

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateSupplierRequest
 *
 * Validates and authorizes updates to an existing supplier. The unique
 * email rule ignores the supplier currently being edited — otherwise
 * saving the form without changing the email would fail validation
 * against itself.
 */
class UpdateSupplierRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'suppliers.edit' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('suppliers.edit');
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
                Rule::unique('suppliers', 'email')->ignore($this->route('supplier')),
            ],
            'phone'        => ['required', 'string', 'max:20'],
            'address'      => ['nullable', 'string', 'max:1000'],
            'company_name' => ['required', 'string', 'max:255'],
            'status'       => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'A supplier with this email already exists.',
        ];
    }
}
