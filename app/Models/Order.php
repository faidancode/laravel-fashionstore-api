<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'address_snapshot',
        'subtotal_price',
        'discount_price',
        'shipping_price',
        'total_price',
        'note',
        'placed_at',
        'paid_at',
        'cancelled_at',
        'cancel_reason',
        'completed_at',
        'receipt_no',
        'snap_token',
        'snap_redirect_url',
    ];

    protected $casts = [
        'address_snapshot' => 'array',
        'subtotal_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'shipping_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'placed_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
