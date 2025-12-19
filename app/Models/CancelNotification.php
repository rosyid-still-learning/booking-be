<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancelNotification extends Model
{
    protected $fillable = ['booking_id', 'is_read'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
CancelNotification::create([
    'booking_id' => $booking->id,
]);