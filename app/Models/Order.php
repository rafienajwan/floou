<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'recipient_name',
        'recipient_phone',
        'total_price',
        'status',
        'shipping_address',
        'shipping_method',
        'shipping_cost',
        'notes',
        'completed_at',
        'canceled_at',
        'cancel_reason'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'completed_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    protected $appends = ['grand_total'];

    public function getGrandTotalAttribute()
    {
        return $this->total_price + $this->shipping_cost;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function canCancel()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canReview()
    {
        return $this->status === 'completed';
    }

    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->latest('id')
            ->first();

        if ($lastOrder && $lastOrder->invoice_number) {
            $lastNumber = intval(substr($lastOrder->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $date . '-' . $newNumber;
    }
}
