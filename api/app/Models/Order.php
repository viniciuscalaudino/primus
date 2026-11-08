<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
   use HasFactory;

   public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
   public const STATUS_PAID = 'paid';
   public const STATUS_SHIPPED = 'shipped';
   public const STATUS_DELIVERED = 'delivered';
   public const STATUS_CANCELLED = 'cancelled';

   protected $fillable = [
        'user_id',
        'status',
        'subtotal',
        'shipping',
        'total',
        'shipping_recipient_name',
        'shipping_zip_code',
        'shipping_street',
        'shipping_number',
        'shipping_complement',
        'shipping_neighborhood',
        'shipping_city',
        'shipping_state',
   ];

   protected function casts(): array
   {
    return [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
   }

    public const TRANSITIONS = [
        self::STATUS_AWAITING_PAYMENT => [self::STATUS_PAID, self::STATUS_CANCELLED],
        self::STATUS_PAID             => [self::STATUS_SHIPPED],
        self::STATUS_SHIPPED          => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED        => [],
        self::STATUS_CANCELLED        => [],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
