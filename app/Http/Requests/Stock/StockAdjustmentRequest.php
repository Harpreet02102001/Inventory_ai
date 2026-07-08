<?php

declare(strict_types=1);

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StockAdjustmentRequest
 *
 * Validates a stock correction. Unlike StockUpdateRequest (add/reduce),
 * 'reason' is REQUIRED here, not optional — an adjustment overrides the
 * system's own record of stock, so it must always be explained for
 * anyone reviewing the audit trail later.
 */
class StockAdjustmentRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'stock.adjust' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('stock.adjust');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'new_quantity' => ['required', 'integer', 'min:0'],
            'reason'       => ['required', 'string', 'max:255'],
        ];
    }
}
