<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'tags'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function galleries()
    {
        return $this->hasMany(ProductGallery::class);
    }
}
