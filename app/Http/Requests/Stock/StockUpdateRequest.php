<?php

declare(strict_types=1);

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StockUpdateRequest
 *
 * Validates a routine stock add or reduce. Deliberately does NOT check
 * "would this go negative" — that depends on the product's current
 * stock at the exact instant the transaction runs, which this class has
 * no visibility into. That check correctly lives inside
 * StockService::adjustStock(), against the freshly locked row.
 */
class StockUpdateRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'stock.update' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('stock.update');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type'     => ['required', Rule::in(['add', 'reduce'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
