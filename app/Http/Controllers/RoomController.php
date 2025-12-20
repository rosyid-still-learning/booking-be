<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class RoomController extends Controller
{
    /**
     * ===============================
     * LIST RUANGAN (PUBLIC & ADMIN)
     * ===============================
     */
    public function index()
    {
        return response()->json([
            'data' => Room::all()
        ]);
    }

    /**
     * ===============================
     * TEST CLOUDINARY (DEBUG)
     * ===============================
     */
    public function testCloudinary()
    {
        return response()->json([
            'cloud'  => config('cloudinary.cloud_name'),
            'key'    => config('cloudinary.api_key'),
            'secret' => config('cloudinary.api_secret') ? 'ADA' : 'KOSONG',
        ]);
    }

    /**
     * ===============================
     * CREATE ROOM (ADMIN)
     * ===============================
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'category'    => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // 🔧 facilities: string → array
        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // ☁️ UPLOAD KE CLOUDINARY
        if ($request->hasFile('image')) {
            $upload = Cloudinary::upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'rooms']
            );

            // SIMPAN URL LANGSUNG
            $validated['image'] = $upload->getSecurePath();
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data'    => $room
        ], 201);
    }

    /**
     * ===============================
     * DETAIL ROOM
     * ===============================
     */
    public function show(Room $room)
    {
        return response()->json($room);
    }

    /**
     * ===============================
     * UPDATE ROOM (ADMIN)
     * ===============================
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required|string',
            'category'    => 'required|string',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['facilities'] = array_map(
            'trim',
            explode(',', $request->facilities)
        );

        // ☁️ UPDATE IMAGE KE CLOUDINARY
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
            'data'    => $room
        ]);
    }

    /**
     * ===============================
     * DELETE ROOM (ADMIN)
     * ===============================
     */
    public function destroy(Request $request, Room $room)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}
