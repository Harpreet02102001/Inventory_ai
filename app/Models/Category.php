<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Category Model
 *
 * Groups products into logical categories for filtering and reporting.
 * Uses SoftDeletes so deleted categories are hidden from UI but preserved
 * in the database to maintain integrity of historical product records.
 *
 * @property int    $id
 * @property string $name
 * @property string $status  active|inactive
 */
class Category extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    /**
     * Scope to return only active categories.
     *
     * Used in product create/edit forms to prevent assigning
     * a product to an inactive category.
     *
     * Usage: Category::active()->get()
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    /**
     * Get all products belonging to this category.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
