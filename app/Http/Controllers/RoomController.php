<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoomController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Room::all()
        ]);
    }

    public function show(Room $room)
    {
        return response()->json($room);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'facilities' => 'required',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // facilities → array
        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        // 🔥 UPLOAD KE CLOUDINARY (REST API)
        if ($request->hasFile('image')) {
            try {
                $response = Http::asMultipart()->post(
                    'https://api.cloudinary.com/v1_1/' .
                        config('cloudinary.cloud_name') .
                        '/image/upload',
                    [
                        [
                            'name' => 'file',
                            'contents' => fopen($request->file('image')->getRealPath(), 'r'),
                        ],
                        [
                            'name' => 'upload_preset',
                            'contents' => config('cloudinary.upload_preset'),
                        ],
                        [
                            'name' => 'folder',
                            'contents' => 'rooms',
                        ],
                    ]
                );

                if (!$response->successful()) {
                    throw new \Exception($response->body());
                }

                $validated['image'] = $response->json()['secure_url'];
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data' => $room
        ], 201);
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'required|integer',
            'facilities' => 'required',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (is_string($request->facilities)) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $request->facilities)
            );
        }

        if ($request->hasFile('image')) {
            try {
                $response = Http::asMultipart()->post(
                    'https://api.cloudinary.com/v1_1/' .
                        config('cloudinary.cloud_name') .
                        '/image/upload',
                    [
                        [
                            'name' => 'file',
                            'contents' => fopen($request->file('image')->getRealPath(), 'r'),
                        ],
                        [
                            'name' => 'upload_preset',
                            'contents' => config('cloudinary.upload_preset'),
                        ],
                        [
                            'name' => 'folder',
                            'contents' => 'rooms',
                        ],
                    ]
                );

                if (!$response->successful()) {
                    throw new \Exception($response->body());
                }

                $validated['image'] = $response->json()['secure_url'];
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Upload gambar gagal',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $room->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $room
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
