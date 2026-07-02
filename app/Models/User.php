<?php

namespace App\Models;

use App\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 *
 * Represents an authenticated identity in the system.
 * Role assignment and permission checking are handled via the HasRoles trait.
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $status   active|inactive
 */
class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * Mass assignable attributes.
     *
     * Note: password is included here because we hash it before saving
     * in the service layer. Never store plain text passwords.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * Attributes excluded from serialization (e.g., when returning JSON).
     *
     * This ensures passwords and tokens never leak into API responses or logs.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute type casting.
     *
     * Eloquent automatically converts these columns to their PHP equivalents:
     * - email_verified_at: raw datetime string → Carbon instance
     * - password: plain string → hashed string on set (via 'hashed' cast, Laravel 10+)
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    /**
     * Scope to filter only active users.
     *
     * Usage: User::active()->get()
     * Instead of: User::where('status', 'active')->get()
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    /**
     * Get all stock history records created by this user.
     *
     * Every time a user adjusts stock, a record is written here.
     * This gives us a complete audit trail per user.
     *
     * @return HasMany<StockHistory>
     */
    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }

    /**
     * Get all purchase orders created by this user.
     *
     * @return HasMany<PurchaseOrder>
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get all sales created by this user.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // ─────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────

    /**
     * Check if the user account is active.
     *
     * Used in middleware to block deactivated accounts from accessing the system
     * even if their session is still valid.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
