<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class CartItem extends Model
{
    protected static function booted(): void
    {
        $recordActivity = static function (CartItem $item): void {
            DB::table('users')->where('id', $item->user_id)->update([
                'cart_activity_at' => now(),
            ]);
        };

        static::saved($recordActivity);
        static::deleted($recordActivity);
    }

    protected $fillable = [

        'user_id',

        'product_id',

        'quantity',

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
