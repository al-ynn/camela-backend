<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [

        'user_id',

        'order_number',

        'subtotal',

        'shipping_fee',

        'discount',

        'tax',

        'grand_total',

        'payment_status',

        'order_status',

        'payment_method',

        'payment_reference',

        'paid_at',

    ];

    protected function casts(): array
    {
        return [

            'paid_at' => 'datetime',

            'subtotal' => 'decimal:2',

            'shipping_fee' => 'decimal:2',

            'discount' => 'decimal:2',

            'tax' => 'decimal:2',

            'grand_total' => 'decimal:2',


        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    
}