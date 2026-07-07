<?php

declare(strict_types=1);

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePurchaseOrderRequest
 *
 * Validates the PO header plus its array of line items in one request.
 * Note what's absent: reference_number, status, total_amount, and
 * total_price are NOT accepted here at all — they're calculated
 * server-side in PurchaseOrderService, never taken from client input.
 */
class StorePurchaseOrderRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'purchase-orders.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('purchase_orders.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'notes'       => ['nullable', 'string', 'max:1000'],

            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id', 'distinct'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.unit_price'     => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required'             => 'Add at least one product to this order.',
            'items.*.product_id.exists'  => 'One of the selected products is invalid.',
            'items.*.product_id.distinct' => 'The same product was added more than once — combine it into a single line instead.',
            'items.*.quantity.min'       => 'Quantity must be at least 1.',
            'items.*.unit_price.min'     => 'Unit price must be greater than zero.',
        ];
    }
}
