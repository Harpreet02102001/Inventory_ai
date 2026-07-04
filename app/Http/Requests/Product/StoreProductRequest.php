<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreProductRequest
 *
 * Validates and authorizes creation of a new product. This is the most
 * complex Form Request so far — it validates two foreign keys, a
 * cross-field price comparison, and an uploaded image.
 */
class StoreProductRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'products.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('products.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],

            'name'        => ['required', 'string', 'max:255'],
            'sku'         => ['required', 'string', 'max:100', 'unique:products,sku'],
            'description' => ['nullable', 'string', 'max:2000'],

            'purchase_price' => ['required', 'numeric', 'min:0'],
            // gt:purchase_price compares against the OTHER field submitted
            // in this same request — no manual comparison code needed.
            'selling_price'  => ['required', 'numeric', 'gt:purchase_price'],

            'stock_quantity'      => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],

            // 'image' (not 'file') restricts uploads to actual image MIME types.
            // max:2048 is in kilobytes, so this caps uploads at 2MB.
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.unique'             => 'This SKU is already in use by another product.',
            'selling_price.gt'       => 'Selling price must be greater than the purchase price.',
            'category_id.exists'     => 'The selected category is invalid.',
            'supplier_id.exists'     => 'The selected supplier is invalid.',
            'image.mimes'            => 'Image must be a JPG, PNG, or WEBP file.',
            'image.max'              => 'Image must not exceed 2MB.',
        ];
    }
}
