<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminRoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required|array',
            'description' => 'nullable|string',
            'category'    => 'nullable|string',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        Room::create($data);

        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);
        return view('admin.rooms.edit', compact('room'));
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name'        => 'required|string',
            'location'    => 'required|string',
            'capacity'    => 'required|integer',
            'facilities'  => 'required|array',
            'description' => 'nullable|string',
            'category'    => 'nullable|string',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($room->image && Storage::disk('public')->exists($room->image)) {
                Storage::disk('public')->delete($room->image);
            }

            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($data);

        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        if ($room->image && Storage::disk('public')->exists($room->image)) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        return redirect()->route('admin.rooms.index')->with('success', 'Ruangan berhasil dihapus');
    }
}
