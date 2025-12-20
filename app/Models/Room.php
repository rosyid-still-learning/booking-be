<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capacity',
        'facilities',
        'description',
        'category',
        'image',
    ];

    protected $casts = [
        'facilities' => 'array',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        // image SUDAH URL CLOUDINARY
        return $this->image;
    }
}
