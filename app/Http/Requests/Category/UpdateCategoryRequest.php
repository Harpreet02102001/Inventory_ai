<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UpdateCategoryRequest
 *
 * Validates and authorizes updates to an existing category.
 */
class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the authenticated user may edit categories.
     *
     * @return bool True if the user holds the 'categories.edit' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('categories.edit');
    }

    /**
     * Validation rules for updating a category.
     *
     * $this->route('category') gives us the Category model instance via
     * implicit route model binding, so ignore() excludes its own row
     * from the uniqueness check.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists.',
        ];
    }
}
