<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    // ===============================
    // GET ALL ROOMS
    // ===============================
    public function index()
    {
        return response()->json([
            'data' => Room::all()
        ]);
    }

    // ===============================
    // STORE ROOM (NO FILE UPLOAD!)
    // ===============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required',
            'description' => 'nullable|string',
            'category'    => 'nullable|string',
            'image'       => 'nullable|string', // 🔥 URL DARI CLOUDINARY
        ]);

        // facilities -> array
        if (is_string($validated['facilities'])) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $validated['facilities'])
            );
        }

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan',
            'data' => $room
        ], 201);
    }

    // ===============================
    // SHOW ROOM
    // ===============================
    public function show(Room $room)
    {
        return response()->json($room);
    }

    // ===============================
    // UPDATE ROOM
    // ===============================
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required',
            'description' => 'nullable|string',
            'category'    => 'required|string',
            'image'       => 'nullable|string', // URL BARU
        ]);

        if (is_string($validated['facilities'])) {
            $validated['facilities'] = array_map(
                'trim',
                explode(',', $validated['facilities'])
            );
        }

        $room->update($validated);

        return response()->json([
            'message' => 'Ruangan berhasil diperbarui',
            'data' => $room
        ]);
    }

    // ===============================
    // DELETE ROOM
    // ===============================
    public function destroy(Request $request, Room $room)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted']);
    }
}
