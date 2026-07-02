<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product Model
 *
 * The central inventory entity. Belongs to a category and supplier.
 * Tracks pricing, stock quantity, and image.
 *
 * @property int    $id
 * @property int    $category_id
 * @property int    $supplier_id
 * @property string $sku
 * @property float  $purchase_price
 * @property float  $selling_price
 * @property int    $stock_quantity
 * @property int    $low_stock_threshold
 * @property string $status  active|inactive
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'sku',
        'description',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'low_stock_threshold',
        'image',
        'status',
    ];

    /**
     * Attribute type casting.
     *
     * Ensures prices come back as floats (not strings) and quantities as integers.
     * This matters when doing arithmetic — "10.00" + 5 in PHP has edge cases.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price'     => 'decimal:2',
            'selling_price'      => 'decimal:2',
            'stock_quantity'     => 'integer',
            'low_stock_threshold' => 'integer',
        ];
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    /**
     * Scope to return only active products.
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to return products at or below their low stock threshold.
     *
     * Uses a column comparison (not hardcoded 10) so each product's
     * individual threshold is respected.
     *
     * Usage: Product::lowStock()->get()
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    /**
     * Get the category this product belongs to.
     *
     * @return BelongsTo<Category, Product>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier this product is sourced from.
     *
     * @return BelongsTo<Supplier, Product>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the stock history records for this product.
     *
     * Ordered by latest first — most useful default for audit displays.
     *
     * @return HasMany<StockHistory>
     */
    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class)->latest();
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Check if this product is currently low on stock.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    /**
     * Get the full URL to this product's image.
     *
     * Returns a placeholder if no image is set, so views
     * never need to handle a null image themselves.
     *
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/placeholder.png');
    }
}
