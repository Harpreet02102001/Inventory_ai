<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Supplier Model
 *
 * Represents a vendor or supplier that provides products.
 * Linked to products and purchase orders.
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $company_name
 * @property string $status  active|inactive
 */
class Supplier extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'status',
    ];

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    /**
     * Scope to return only active suppliers.
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
     * Get all products sourced from this supplier.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all purchase orders placed with this supplier.
     *
     * @return HasMany<PurchaseOrder>
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
