<?php

declare(strict_types=1);

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreSupplierRequest
 *
 * Validates and authorizes creation of a new supplier. Runs automatically
 * before SupplierController::store() — if authorize() fails, a 403 is
 * thrown; if rules() fail, the user is redirected back with errors and
 * the controller method body never executes.
 */
class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the authenticated user may create a supplier.
     *
     * @return bool True if the user holds the 'suppliers.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('suppliers.create');
    }

    /**
     * Validation rules for creating a supplier.
     *
     * Phone is validated as a string, not numeric — real phone numbers
     * commonly include +, -, spaces, and leading zeros, all of which a
     * numeric rule would incorrectly reject or corrupt.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:suppliers,email'],
            'phone'        => ['required', 'string', 'max:13'],
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
