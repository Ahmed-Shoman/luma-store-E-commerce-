<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'description',
        'style', 'is_best_seller', 'is_trending_now',
        'is_new_arrival', 'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
