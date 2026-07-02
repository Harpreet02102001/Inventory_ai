<?php

namespace App\Models;

use App\Models\User;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;

// Sale
class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'reference_number',
        'status',
        'total_amount',
        'discount_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'    => 'decimal:2',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
