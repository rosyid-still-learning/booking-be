<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'title',
        'purpose',
        'start_time',
        'end_time',
        'attachment',
        'status',
    ];

    protected $appends = ['attachment_url'];

    public function getAttachmentUrlAttribute()
    {
        // attachment SUDAH URL CLOUDINARY
        return $this->attachment;
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
