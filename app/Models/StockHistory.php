<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockHistory Model
 *
 * An immutable audit record of every stock change in the system.
 * Rows are never updated or deleted — this is an append-only audit log.
 *
 * @property int    $id
 * @property int    $product_id
 * @property int    $user_id
 * @property int    $old_quantity
 * @property int    $changed_quantity  Positive = added, Negative = reduced
 * @property int    $new_quantity
 * @property string $type  add|reduce|purchase|sale|adjustment
 */
class StockHistory extends Model
{
    /**
     * Disable updated_at — stock history rows are immutable once written.
     * Setting $timestamps = false would also disable created_at which we need,
     * so we only disable updated_at by setting the constant to null.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'user_id',
        'old_quantity',
        'changed_quantity',
        'new_quantity',
        'type',
        'remarks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_quantity'     => 'integer',
            'changed_quantity' => 'integer',
            'new_quantity'     => 'integer',
            'created_at'       => 'datetime',
        ];
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    /**
     * Get the product this stock change belongs to.
     *
     * @return BelongsTo<Product, StockHistory>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made this stock change.
     *
     * @return BelongsTo<User, StockHistory>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
