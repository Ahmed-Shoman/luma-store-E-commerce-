<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_address',
        'total',
        'status',
    ];

    protected $casts = [
        'total' => 'integer',
    ];

    /*
    |----------------------------------------------------
    | RELATIONS
    |----------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /*
    |----------------------------------------------------
    | SCOPES (VERY IMPORTANT FOR PERFORMANCE)
    |----------------------------------------------------
    */

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderByDesc('created_at');
    }

    /*
    |----------------------------------------------------
    | HELPERS
    |----------------------------------------------------
    */

    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
}
