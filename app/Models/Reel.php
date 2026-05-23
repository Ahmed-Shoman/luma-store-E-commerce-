<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reel extends Model
{
    protected $fillable = [
        'video',
        'product_id',
    ];

    protected $appends = [
        'video_url',
    ];

    // Relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Fast accessor (no asset() overhead)
    public function getVideoUrlAttribute(): string
    {
        return asset('storage/' . $this->video);
    }
}
