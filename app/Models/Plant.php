<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image',
        'category_id',
        'plant_type_id'
    ];

    protected $appends = ['image_url'];

    // Accessor untuk mendapatkan URL gambar
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        return url('storage/' . $this->image);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function plantType()
    {
        return $this->belongsTo(PlantType::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
