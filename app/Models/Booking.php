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
    return $this->attachment
        ? asset('storage/' . $this->attachment)
        : null;
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
