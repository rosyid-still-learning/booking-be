@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Tambah Ruangan</h2>

    <form action="{{ route('admin.rooms.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- NAMA --}}
        <div class="mb-3">
            <label class="form-label">Nama Ruangan</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        {{-- LOKASI --}}
        <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        {{-- KAPASITAS --}}
        <div class="mb-3">
            <label class="form-label">Kapasitas</label>
            <input type="number" name="capacity" class="form-control" required>
        </div>

        {{-- KATEGORI --}}
        <div class="mb-3">
            <label class="form-label">Kategori</label>
            <input type="text" name="category" class="form-control">
        </div>

        {{-- FASILITAS --}}
        <div class="mb-3">
            <label class="form-label">Fasilitas (Pisahkan dengan koma)</label>
            <input type="text" name="facilities" class="form-control" placeholder="AC, Proyektor, Kursi">
        </div>

        {{-- DESKRIPSI --}}
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        {{-- STATUS --}}
        <div class="mb-3">
            <label class="form-label">Status Aktif</label>
            <select name="is_active" class="form-select">
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
            </select>
        </div>

        {{-- FOTO --}}
        <div class="mb-3">
            <label class="form-label">Foto Ruangan</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
