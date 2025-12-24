<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Cloudinary\Cloudinary;



class BookingController extends Controller
{
    /**
     * GET /bookings
     * Ambil semua booking milik user yang sedang login
     */
    public function index()
{
    $bookings = Booking::with('room')
        ->where('user_id', auth()->id())
        ->latest()
        ->get();

    return response()->json([
        'data' => $bookings
    ]);
}


    /**
     * POST /bookings
     * Membuat booking baru
     */
    public function store(Request $request)
{
    // ❌ ADMIN TIDAK BOLEH MEMBUAT BOOKING
    if (auth()->user()->role !== 'user') {
        return response()->json([
            'message' => 'Hanya user yang dapat membuat booking.'
        ], 403);
    }

    // 🟢 Validasi baru (ada purpose + attachment)
    $validated = $request->validate([
        'room_id' => 'required|exists:rooms,id',
        'title' => 'required|string',
        'purpose' => 'nullable|string',
        'start_time' => 'required|date|before:end_time',
        'end_time' => 'required|date|after:start_time',
        'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
    ]);

    $validated['user_id'] = auth()->id();
    $validated['status'] = 'pending';

    // 🟢 Cek bentrok hanya yang approved
    $conflict = Booking::where('room_id', $validated['room_id'])
        ->where('status', 'approved')
        ->where(function ($q) use ($validated) {
            $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
              ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
              ->orWhere(function ($q2) use ($validated) {
                  $q2->where('start_time', '<=', $validated['start_time'])
                     ->where('end_time', '>=', $validated['end_time']);
              });
        })
        ->exists();

    if ($conflict) {
        return response()->json([
            'message' => 'Ruangan sudah dibooking pada waktu tersebut.'
        ], 409);
    }

 // ================= UPLOAD FILE (FINAL – STABIL) =================
// ================= UPLOAD FILE (FINAL STABIL) =================
$fileUrl = null;

if ($request->hasFile('attachment')) {
    try {
        $cloudinary = new \Cloudinary\Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);

        $file = $request->file('attachment');

        $result = $cloudinary->uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'attachments',
                'resource_type' => 'auto', // 🔥 PENTING: JANGAN RAW
                'use_filename' => true,     // pakai nama asli
                'unique_filename' => false // jangan diacak
            ]
        );

        $fileUrl = $result['secure_url'];

    } catch (\Throwable $e) {
        \Log::error('Cloudinary upload error', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Upload file gagal'
        ], 500);
    }
}








    // 🟢 Simpan booking
    $booking = Booking::create([
        'user_id' => auth()->id(),
        'room_id' => $validated['room_id'],
        'title' => $validated['title'],
        'purpose' => $validated['purpose'] ?? null,
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'attachment' => $fileUrl,
        'status' => 'pending'
    ]);

    return response()->json([
        'message' => 'Booking berhasil dibuat.',
        'data' => $booking
    ], 201);
}


    /**
     * GET /bookings/{id}
     * Ambil detail booking
     */
    public function show($id)
    {
        $booking = Booking::with('room', 'user')->find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail booking berhasil diambil.',
            'data' => $booking
        ]);
    }

    /**
     * PUT /bookings/{id}
     * Update booking
     */
    public function update(Request $request, $id)
    {
        // ❌ ADMIN TIDAK BOLEH EDIT BOOKING
        if (auth()->user()->role !== 'user') {
            return response()->json([
                'message' => 'Hanya user yang dapat mengedit booking.'
            ], 403);
        }

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking tidak ditemukan.'
            ], 404);
        }

        // User hanya bisa edit booking miliknya
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk mengedit booking ini.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'start_time' => 'required|date|before:end_time',
            'end_time' => 'required|date|after:start_time',
        ]);

        // ✅ Cek bentrok terhadap booking APPROVED saja
        $conflict = Booking::where('room_id', $booking->room_id)
            ->where('id', '!=', $booking->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_time', '<=', $validated['start_time'])
                         ->where('end_time', '>=', $validated['end_time']);
                  });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Ruangan sudah dibooking pada waktu tersebut.'
            ], 409);
        }

        // Simpan perubahan
        $booking->update($validated);

        return response()->json([
            'message' => 'Booking berhasil diperbarui.',
            'data' => $booking
        ]);
    }

    /**
     * DELETE /bookings/{id}
     * Hapus booking
     */
    public function destroy($id)
    {
        // ❌ ADMIN TIDAK BOLEH DELETE BOOKING
        if (auth()->user()->role !== 'user') {
            return response()->json([
                'message' => 'Hanya user yang dapat menghapus booking.'
            ], 403);
        }

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking tidak ditemukan.'
            ], 404);
        }

        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Anda tidak memiliki izin untuk menghapus booking ini.'
            ], 403);
        }

        $booking->delete();

        return response()->json([
            'message' => 'Booking berhasil dihapus.'
        ]);
    }

    // ================================
    // ADMIN: GET Semua Booking (Diperbarui)
    // ================================
    /**
     * Ambil semua booking (Khusus Admin)
     */
    public function adminIndex(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $bookings = Booking::with(['room', 'user'])
        ->where('status', 'pending') // ✅ FIX UTAMA
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'data' => $bookings
    ]);
}

    
    // ================================
    // ADMIN: GET Booking Berstatus Pending
    // ================================
    /**
     * Ambil semua booking dengan status 'pending' (Khusus Admin)
     */
    public function adminPendingBookings(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized (Admin Only)'
            ], 403);
        }

        $bookings = Booking::with(['room', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Booking pending berhasil diambil.',
            'data' => $bookings
        ]);
    }


    // ================================
    // ADMIN: Update Status Booking
    // ================================
    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['message' => 'Booking tidak ditemukan'], 404);
        }

        $booking->status = $validated['status'];
        $booking->save();

        return response()->json([
            'message' => 'Status booking diperbarui.',
            'data' => $booking
        ]);
    }

    // ================================
    // ADMIN: Booking Per Ruangan
    // ================================
    public function bookingsByRoom(Request $request, $room_id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookings = Booking::where('room_id', $room_id)
            ->with('user')
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json([
            'message' => 'Booking ruangan berhasil diambil.',
            'data' => $bookings
        ]);
    }

    // ================================
    // ADMIN: Approve Booking (Versi Baru)
    // ================================
    /**
     * Menyetujui booking (Khusus Admin)
     */
    public function approveBooking($id)
    {
        // ✅ Tambahkan cek role admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $booking = Booking::findOrFail($id);
        $booking->status = 'approved';
        $booking->save();

        return response()->json(['message' => 'Booking approved']);
    }
    

    // ================================
    // ADMIN: Reject Booking (Versi Baru)
    // ================================
    /**
     * Menolak booking (Khusus Admin)
     */
    public function rejectBooking($id)
    {
        // ✅ Tambahkan cek role admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $booking = Booking::findOrFail($id);
        $booking->status = 'rejected';
        $booking->save();

        return response()->json(['message' => 'Booking rejected']);
        
    }
    
    // ================================
    // ADMIN: Menghitung Booking Pending
    // ================================
    /**
     * Menghitung jumlah booking dengan status 'pending' (Khusus Admin)
     */
    public function pendingCount()
    {
        // ✅ Tambahkan cek role admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $count = Booking::where('status', 'pending')->count();

        return response()->json([
            'count' => $count
        ]);
    }
    // GET /rooms/{id}/bookings
public function bookingsByRoomPublic($room_id)
{
    $bookings = Booking::where('room_id', $room_id)
        ->where('status', 'approved') // 🔴 HANYA APPROVED
        ->select('id', 'start_time', 'end_time', 'purpose', 'status')
        ->get();

    return response()->json([
        'data' => $bookings
    ]);

    
}
public function cancel($id)
{
    $booking = Booking::with('room')->find($id);

    if (!$booking) {
        return response()->json(['message' => 'Booking tidak ditemukan'], 404);
    }

    if ($booking->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ❌ hanya boleh cancel jika masih pending / approved
    if (!in_array($booking->status, ['pending', 'approved'])) {
        return response()->json(['message' => 'Booking tidak bisa dibatalkan'], 400);
    }

    // ⏱️ aturan H-24 JAM
    $start = Carbon::parse($booking->start_time);
    if (now()->diffInHours($start, false) < 24) {
        return response()->json([
            'message' => 'Pembatalan hanya bisa dilakukan minimal 24 jam sebelum pemakaian.'
        ], 400);
    }

    // ubah status
    $booking->status = 'cancelled';
    $booking->save();

    // 🔔 NOTIFIKASI ADMIN
    DB::table('admin_notifications')->insert([
        'message' => 'User membatalkan booking ruangan ' . ($booking->room->name ?? ''),
        'is_read' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message' => 'Booking berhasil dibatalkan'
    ]);
}

public function adminNotifications(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $notifications = DB::table('admin_notifications')
        ->where('is_read', false)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($notifications);
}

public function clearNotifications(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    DB::table('admin_notifications')->update(['is_read' => true]);

    return response()->json(['message' => 'OK']);
}


public function adminDelete($id)
{
    if (auth()->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $booking = Booking::findOrFail($id);
    $booking->delete();

    return response()->json(['message' => 'Booking dihapus']);
}

public function history(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $bookings = Booking::with(['room','user'])
        ->whereIn('status', ['approved', 'rejected', 'cancelled']) // ✅ FIX
        ->latest()
        ->get();

    return response()->json(['data' => $bookings]);
}


}


