<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Tampilkan daftar semua ruangan.
     */
    public function index()
{
    return response()->json([
        'data' => Room::all()
    ]);
}

public function testCloudinary()
{
    return response()->json([
        'cloud' => config('cloudinary.cloud_name'),
        'key' => config('cloudinary.api_key'),
        'secret' => config('cloudinary.api_secret') ? 'ADA' : 'KOSONG',
    ]);
}


    /**
     * Simpan ruangan baru.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'location' => 'required|string',
        'capacity' => 'required|integer',
        'facilities' => 'required',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'category' => 'nullable|string',
        'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
    ]);

   // 🔥 FIX FACILITIES (STRING → ARRAY)
if (is_string($request->facilities)) {
    $validated['facilities'] = array_map(
        'trim',
        explode(',', $request->facilities)
    );
} else {
    $validated['facilities'] = $request->facilities;
}


    // Upload image
    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('rooms', 'public');
    }

    $room = Room::create($validated);

    return response()->json([
        'message' => 'Ruangan berhasil ditambahkan',
        'data' => $room
    ], 201);
}

    /**
     * Tampilkan detail ruangan tertentu.
     */
    public function show(Room $room)
    {
        // $casts otomatis bekerja saat model dimuat.
        return response()->json($room);
    }

    /**
     * Perbarui ruangan tertentu.
     */
   public function update(Request $request, Room $room)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'location' => 'required|string',
        'capacity' => 'required|integer',
        'facilities' => 'required|string',
        'category' => 'required|string',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'image' => 'nullable|image|max:2048',
    ]);

    $validated['facilities'] = array_map(
        'trim',
        explode(',', $request->facilities)
    );

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('rooms', 'public');
    }

    $room->update($validated);

    return response()->json([
        'message' => 'Ruangan berhasil diperbarui',
        'data' => $room
    ]);


// 🔥 decode facilities
$validated['facilities'] = json_decode($validated['facilities'], true);


    // Upload foto bila ada file baru
    if ($request->hasFile('image')) {
        // Hapus gambar lama (optional)
        if ($room->image && \Storage::disk('public')->exists($room->image)) {
            \Storage::disk('public')->delete($room->image);
        }

        $validated['image'] = $request->file('image')->store('rooms', 'public');
    }

    $room->update($validated);

    return response()->json([
        'message' => 'Ruangan berhasil diperbarui',
        'data' => $room
    ], 200);
}


    /**
     * Hapus ruangan tertentu.
     */
    public function destroy(Request $request, Room $room)
    {
        // Hanya admin yang boleh menghapus ruangan.
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}