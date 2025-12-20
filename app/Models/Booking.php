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
        return $this->attachment;
    

        // ✅ Jika sudah URL cloudinary
        if (str_starts_with($this->attachment, 'http')) {
            return $this->attachment;
        }

        // 🔥 FIX DATA LAMA (PATH → URL CLOUDINARY)
        return 'https://res.cloudinary.com/dgkajfw1b/image/upload/' . $this->attachment;
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
