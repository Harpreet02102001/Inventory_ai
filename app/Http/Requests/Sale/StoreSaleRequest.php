<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreSaleRequest
 *
 * Validates the sale header plus its array of line items. Note what's
 * absent: reference_number, status, and total_amount are never accepted
 * from the client — all calculated server-side in SaleService.
 */
class StoreSaleRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'sales.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('sales.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:1000'],

            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required'              => 'Add at least one product to this sale.',
            'items.*.product_id.exists'    => 'One of the selected products is invalid.',
            'items.*.product_id.distinct'  => 'The same product was added more than once — combine it into a single line instead.',
            'items.*.quantity.min'         => 'Quantity must be at least 1.',
            'items.*.unit_price.min'       => 'Unit price must be greater than zero.',
        ];
    }
}
