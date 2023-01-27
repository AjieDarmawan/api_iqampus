@extends('components.index')

@section('content')
    <a href="{{ route('masterKampus.showinsert') }}" class="btn btn-primary my-3">Tambah</a>
    <table class="table table-striped table-hover table-bordered">
        <thead>
            <tr>
                <th scope="col">Nama kampus</th>
                <th scope="col">Kode kampus</th>
                <th scope="col">Logo Kampus</th>
                <th scope="col">Alamat Kampus</th>
                <th scope="col">Foto Kampus</th>
                <th scope="col">Profil kampus</th>
                <th scope="col">warna kampus</th>
                <th scope="col">nama rektor</th>
                <th scope="col">foto rektor</th>
                <th scope="col">tgl kerjasama</th>
                <th scope="col">aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($masterKampuses as $kampus)
                <tr>
                    <td>{{ $kampus['nama_kampus'] }}</td>
                    <td>{{ $kampus['kode_kampus'] }}</td>
                    <td><img src="{{ $kampus['logo_kampus'] }}" alt=""></td>
                    <td>{{ $kampus['alamat_kampus'] }}</td>
                    <td>{{ $kampus['foto_kampus'] }}</td>
                    <td>{{ $kampus['profil_kampus'] }}</td>
                    <td><input type="color" value="{{ $kampus['warna_kampus'] }}" disabled></td>
                    <td>{{ $kampus['nama_rektor'] }}</td>
                    <td>{{ $kampus['foto_rektor'] }}</td>
                    <td>{{ $kampus['tgl_kerjasama'] }}</td>
                    <td><a href="{{ route('masterKampus.edit', ['id' => $kampus['id']]) }}"
                            class="btn btn-primary">Edit</a>
                        <a href="{{ route('masterKampus.delete', ['id' => $kampus['id']]) }}" class="btn btn-danger"
                            onclick="confirm('Apakah anda yakin?')">Delete</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
