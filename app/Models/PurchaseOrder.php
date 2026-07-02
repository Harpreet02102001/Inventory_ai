<?php

namespace App\Models;

use App\Models\User;
use App\Models\Supplier;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

// PurchaseOrder
class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',
        'reference_number',
        'status',
        'total_amount',
        'notes',
        'ordered_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'ordered_at'   => 'datetime',
            'received_at'  => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
