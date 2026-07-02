<?php

namespace App\Models;


use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'price',
        'original_price',
        'category_id',
        'is_best_seller',
        'is_new_arrival',
        'is_trending',
        'reel_video',
        'sizes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'is_best_seller' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_trending' => 'boolean',
        'sizes' => 'array',
    ];

    /*
    |-----------------------------------
    | RELATIONS (PERFORMANCE FRIENDLY)
    |-----------------------------------
    */

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }



    /*
    |-----------------------------------
    | SCOPES (FAST FILTERING)
    |-----------------------------------
    */

    public function scopeBestSellers($query)
    {
        return $query->where('is_best_seller', true);
    }

    public function scopeNewArrivals($query)
    {
        return $query->where('is_new_arrival', true);
    }

    public function scopeTrending($query)
    {
        return $query->where('is_trending', true);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('original_price')
            ->whereColumn('price', '<', 'original_price');
    }

    /*
    |-----------------------------------
    | ACCESSORS (LIGHTWEIGHT ONLY)
    |-----------------------------------
    */

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return (int) round((1 - $this->price / $this->original_price) * 100);
        }

        return null;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    public function getReelUrlAttribute(): ?string
    {
        return $this->reel_video ? asset('storage/' . $this->reel_video) : null;
    }

    /*
    |-----------------------------------
    | PERFORMANCE HELPERS
    |-----------------------------------
    */

    // FAST: used in product list only
    public function getMainImageAttribute(): ?string
    {
        return $this->relationLoaded('images')
            ? ($this->images->first()->url ?? null)
            : null;
    }
}
