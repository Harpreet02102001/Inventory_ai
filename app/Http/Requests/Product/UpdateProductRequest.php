<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateProductRequest
 *
 * Same rules as StoreProductRequest, with the SKU uniqueness check
 * ignoring the product currently being edited.
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * @return bool True if the user holds the 'products.edit' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('products.edit');
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
            'sku'         => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'description' => ['nullable', 'string', 'max:2000'],

            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'gt:purchase_price'],

            'stock_quantity'      => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],

            // Image is optional on update — leaving it blank means "keep the current image."
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
            'sku.unique'         => 'This SKU is already in use by another product.',
            'selling_price.gt'   => 'Selling price must be greater than the purchase price.',
            'category_id.exists' => 'The selected category is invalid.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'image.mimes'        => 'Image must be a JPG, PNG, or WEBP file.',
            'image.max'          => 'Image must not exceed 2MB.',
        ];
    }
}
