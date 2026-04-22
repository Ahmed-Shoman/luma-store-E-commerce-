<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

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
        'images',
        'category',
        'is_best_seller',
        'is_new_arrival',
        'is_trending',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'original_price' => 'decimal:2',
        'images'         => 'array',
        'is_best_seller' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_trending'    => 'boolean',
    ];

    // ─── Accessors ────────────────────────────────────────────────────

    public function getImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return (int) round((1 - $this->price / $this->original_price) * 100);
        }
        return null;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return !is_null($this->original_price) && $this->original_price > $this->price;
    }

    // ─── Image Helpers ────────────────────────────────────────────────

    /**
     * Delete all stored image files for this product.
     * Called before delete or when replacing images.
     */
    public function deleteImages(): void
    {
        foreach ($this->images ?? [] as $url) {
            $path = str_replace(Storage::disk('public')->url(''), '', $url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    // ─── Scopes ───────────────────────────────────────────────────────

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

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('original_price')
            ->whereColumn('price', '<', 'original_price');
    }
}
