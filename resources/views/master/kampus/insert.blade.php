@extends('components.index')

@section('content')
    <div class="col-10 my-3 m-auto shadow-sm p-4">

        <form action="{{ route('masterKampus.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-6">
                    <label for="nama" class="form-label">Nama Kampus</label>
                    <input type="text" name="nama_kampus" class="form-control" id="nama">
                </div>
                <div class="col-6">
                    <label for="kode" class="form-label">Kode Kampus</label>
                    <input type="text" name="kode_kampus" class="form-control" id="kode">
                </div>
                <div class="col-6">
                    <label for="tgl_kerjasama" class="form-label">Tanggal Kerja Sama</label>
                    <input type="date" name="tgl_kerjasama" class="form-control" id="tgl_kerjasama">
                </div>
                <div class="col-6">
                    <label for="logo_kampus" class="form-label">Logo Kampus</label>
                    <input type="file" name="logo_kampus" class="form-control" id="logo_kampus">
                </div>
                <div class="col-6">
                    <label for="nama_rektor" class="form-label">Nama Rektor</label>
                    <input type="text" name="nama_rektor" class="form-control" id="nama_rektor">
                </div>
                <div class="col-6">
                    <label for="foto_rektor" class="form-label">Foto Rektor</label>
                    <input type="file" name="foto_rektor" class="form-control" id="foto_rektor">
                </div>
                <div class="col-12">
                    <label for="alamat" class="form-label">Alamat kampus</label>
                    <textarea class="form-control" name="alamat_kampus" id="alamat" rows="3"></textarea>
                </div>
                <div class="col-12">
                    <label for="profil_kampus" class="form-label">Profil kampus</label>
                    <textarea class="form-control" name="profil_kampus" id="profil_kampus" rows="3"></textarea>
                </div>
                <div class="col-6">
                    <label for="foto_kampus" class="form-label">Foto kampus</label>
                    <input type="file" name="foto_kampus" class="form-control" id="foto_kampus">
                </div>
                <div class="col-6">
                    <label for="warna_kampus" class="form-label">Warna/Tema kampus</label>
                    <input type="color" name="warna_kampus" class="form-control form-control-color" id="warna_kampus"
                        value="#FFEA00">
                </div>


                <button type="submit" name="submit" class="btn btn-primary w-100 fm-semibold mt-3">Submit</button>
            </div>

        </form>

    </div>
@endsection
