<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class RoomController extends Controller
{
    /**
     * GET /rooms & /admin/rooms
     */
    public function index()
    {
        return response()->json([
            'data' => Room::all()
        ]);
    }

    /**
     * TEST CLOUDINARY
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
     * POST /admin/rooms
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

        // facilities → array
        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // 🔥 UPLOAD IMAGE KE CLOUDINARY (AMAN)
        if ($request->hasFile('image')) {
            try {
                $upload = Cloudinary::upload(
                    $request->file('image')->getRealPath(),
                    ['folder' => 'rooms']
                );

                $validated['image'] = $upload->getSecurePath();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data'    => $room
        ], 201);
    }

    /**
     * GET /rooms/{room}
     */
    public function show(Room $room)
    {
        return response()->json($room);
    }

    /**
     * PUT /admin/rooms/{room}
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required',
            'category'    => 'required|string',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // 🔥 UPLOAD IMAGE BARU (AMAN + TIDAK 500)
        if ($request->hasFile('image')) {
            try {
                $upload = Cloudinary::upload(
                    $request->file('image')->getRealPath(),
                    ['folder' => 'rooms']
                );

                $validated['image'] = $upload->getSecurePath();
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }

        $room->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data'    => $room
        ]);
    }

    /**
     * DELETE /admin/rooms/{room}
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
