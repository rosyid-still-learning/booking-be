@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Edit Ruangan</h2>

    <form action="{{ route('admin.rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- NAMA --}}
        <div class="mb-3">
            <label class="form-label">Nama Ruangan</label>
            <input type="text" name="name" class="form-control" value="{{ $room->name }}" required>
        </div>

        {{-- LOKASI --}}
        <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <input type="text" name="location" class="form-control" value="{{ $room->location }}" required>
        </div>

        {{-- KAPASITAS --}}
        <div class="mb-3">
            <label class="form-label">Kapasitas</label>
            <input type="number" name="capacity" class="form-control" value="{{ $room->capacity }}" required>
        </div>

        {{-- KATEGORI --}}
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control" value="{{ $room->category }}">
        </div>

        {{-- FASILITAS --}}
        <div class="mb-3">
            <label class="form-label">Fasilitas (pisahkan dengan koma)</label>
            <input type="text" name="facilities" class="form-control"
                   value="{{ implode(', ', $room->facilities ?? []) }}">
        </div>

        {{-- DESKRIPSI --}}
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control">{{ $room->description }}</textarea>
        </div>

        {{-- STATUS --}}
        <div class="mb-3">
            <label class="form-label">Status Aktif</label>
            <select name="is_active" class="form-select">
                <option value="1" {{ $room->is_active ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ !$room->is_active ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        {{-- FOTO --}}
        <div class="mb-3">
            <label class="form-label">Foto Ruangan (opsional)</label>
            <input type="file" name="image" class="form-control">

            @if($room->image_url)
                <img src="{{ $room->image_url }}" width="150" class="mt-2 rounded">
            @endif
        </div>

        <button class="btn btn-success">Update</button>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
