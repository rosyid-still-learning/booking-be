<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

    /**
     * Test Cloudinary config
     */
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

        // facilities → array
        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // 🔥 UPLOAD IMAGE KE CLOUDINARY
        if ($request->hasFile('image')) {
            $upload = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'rooms']
            );

            $validated['image'] = $upload->getSecurePath(); // URL HTTPS
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data' => $room
        ], 201);
    }

    /**
     * Detail ruangan
     */
    public function show(Room $room)
    {
        return response()->json($room);
    }

    /**
     * Update ruangan
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'facilities' => 'required',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // 🔥 UPLOAD IMAGE BARU KE CLOUDINARY
        if ($request->hasFile('image')) {
            $upload = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'rooms']
            );

            $validated['image'] = $upload->getSecurePath();
        }

        $room->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $room
        ]);
    }

    /**
     * Hapus ruangan
     */
    public function destroy(Request $request, Room $room)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ❌ TIDAK PERLU DELETE FILE (CLOUDINARY AMAN)
        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}
