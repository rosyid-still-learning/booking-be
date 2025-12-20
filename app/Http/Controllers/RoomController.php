<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class RoomController extends Controller
{
    /**
     * GET /rooms
     */
    public function index()
    {
        return response()->json([
            'data' => Room::all()
        ]);
    }

    /**
     * POST /admin/rooms
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'facilities' => 'required',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // facilities → array
        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // ===== CLOUDINARY UPLOAD (FINAL & AMAN) =====
        if ($request->hasFile('image')) {
            try {
                $upload = Cloudinary::uploadApi()->upload(
                    $request->file('image')->getRealPath(),
                    [
                        'folder' => 'rooms',
                        'upload_preset' => 'rooms_unsigned',
                    ]
                );

                // SIMPAN URL CLOUDINARY
                $validated['image'] = $upload['secure_url'];
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data' => $room,
        ], 201);
    }

    /**
     * GET /admin/rooms/{room}
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
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'facilities' => 'required',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // ===== CLOUDINARY UPDATE IMAGE =====
        if ($request->hasFile('image')) {
            try {
                $upload = Cloudinary::uploadApi()->upload(
                    $request->file('image')->getRealPath(),
                    [
                        'folder' => 'rooms',
                        'upload_preset' => 'rooms_unsigned',
                    ]
                );

                $validated['image'] = $upload['secure_url'];
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $room->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $room,
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
