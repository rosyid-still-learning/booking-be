@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Daftar Ruangan</h2>

    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary mb-3">
        + Tambah Ruangan
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Nama</th>
                <th>Lokasi</th>
                <th>Kapasitas</th>
                <th>Kategori</th>
                <th>Aktif?</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($rooms as $room)
            <tr>
                <td>{{ $room->name }}</td>
                <td>{{ $room->location }}</td>
                <td>{{ $room->capacity }}</td>
                <td>{{ $room->category ?? '-' }}</td>
                <td>{{ $room->is_active ? 'Ya' : 'Tidak' }}</td>
                <td>
                    @if($room->image_url)
                        <img src="{{ $room->image_url }}" width="80" class="rounded">
                    @else
                        <small>Tidak ada</small>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-warning btn-sm">
                        Edit
                    </a>

                    <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus ruangan ini?')">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
