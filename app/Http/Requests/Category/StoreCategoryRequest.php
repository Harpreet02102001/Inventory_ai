<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreCategoryRequest
 *
 * Validates and authorizes creation of a new category. Laravel resolves
 * this automatically when it appears as a type-hinted parameter on
 * CategoryController::store() — authorize() and rules() both run before
 * the controller method body executes.
 */
class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the authenticated user may create a category.
     *
     * can() routes through Gate::before() in AuthServiceProvider, which
     * checks the user's role-based permissions — same mechanism the
     * @can() Blade directive uses in the nav.
     *
     * @return bool True if the user holds the 'categories.create' permission
     */
    public function authorize(): bool
    {
        return $this->user()->can('categories.create');
    }

    /**
     * Validation rules for creating a category.
     *
     * @return array<string, mixed> Field name => rule set
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status'      => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * Friendlier text for specific validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists.',
        ];
    }
}
