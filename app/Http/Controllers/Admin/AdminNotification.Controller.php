<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Notification::where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    public function markAsRead($id)
    {
        $notif = Notification::findOrFail($id);
        $notif->update(['is_read' => true]);

        return response()->json(['message' => 'OK']);
    }
}
