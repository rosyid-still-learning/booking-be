<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class RoomController extends Controller
{
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
            'image'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // facilities → array
        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // ✅ CLOUDINARY UPLOAD (FIXED)
        if ($request->hasFile('image')) {
            try {
                $result = Cloudinary::uploadFile(
                    $request->file('image')->getRealPath(),
                    ['folder' => 'rooms']
                );

                // ⬅️ INI KUNCI UTAMANYA
                $validated['image'] = $result->getSecurePath();
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data'    => $room
        ], 201);
    }

    public function show(Room $room)
    {
        return response()->json($room);
    }

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
            'image'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // ✅ CLOUDINARY UPDATE (FIXED)
        if ($request->hasFile('image')) {
            try {
                $result = Cloudinary::uploadFile(
                    $request->file('image')->getRealPath(),
                    ['folder' => 'rooms']
                );

                $validated['image'] = $result->getSecurePath();
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }

        $room->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data'    => $room
        ]);
    }

    public function destroy(Request $request, Room $room)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}
