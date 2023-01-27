<?php



/** @var \Laravel\Lumen\Routing\Router $router */

use \Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Http\Request;
// use Laravel\Lumen\Routing\Router;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Route::get('/updateapp', function()
// {
//     \Artisan::call('dump-autoload');
//     echo 'dump-autoload complete';
// });

$router->get('/', function () use ($router) {
    // Artisan::call('migrate');
    return "{$router->app->version()} <br>" . Artisan::output();
});
// $router->post('/tes', function (Request $request){
//     return json_encode([
//         "header"=>$request->header(),
//         "body"=>$request->all(),
//         "dll"=>$_SERVER,
//         "dll2"=>$_REQUEST,
//         "dll3"=>gethostbyaddr($request->header('cf-connecting-ip')),
//         "dll4"=>$request->headers->all()
//         // "origin" => $request->header->get('origin')
//     ]);
// });
$router->get('/clean', function () use ($router) {
    Artisan::call('optimize');
});

$router->get('/auth/cek_gelombang', ['middleware' => 'cors', 'uses' => 'AuthController@cek_gelombang']);

$router->post('/auth/jurusan_kampus', ['middleware' => 'cors', 'uses' => 'AuthController@jurusan_kampus']);
$router->post('/auth/jurusan_kampus2', ['middleware' => 'cors', 'uses' => 'AuthController@jurusan_kampus2']);



$router->post('/auth/pilihjenjang', ['middleware' => 'cors', 'uses' => 'AuthController@pilihjenjang']);
$router->post('/auth/pilihkelas', ['middleware' => 'cors', 'uses' => 'AuthController@pilihkelas']);
$router->post('/auth/jurusan_kampus_kelas_jenjang', ['middleware' => 'cors', 'uses' => 'AuthController@jurusan_kampus_kelas_jenjang']);



$router->post('/pmb/mhs/login', ['middleware' => 'cors', 'uses' => 'AuthController@doLoginPmb']);

$router->post('/auth/login', ['middleware' => 'cors', 'uses' => 'AuthController@doLogin']);
$router->post('/auth/loginTesting', ['middleware' => 'cors', 'uses' => 'AuthController@doLoginTesting']);


$router->post('/auth/decode', ['middleware' => 'cors', 'uses' => 'AuthController@decode']);
$router->post('/auth/init', ['middleware' => 'cors', 'uses' => 'AuthController@init']);
$router->post('/auth/init_token', ['middleware' => 'cors', 'uses' => 'AuthController@init_token']);
$router->get('/load/panel', function () {
});
$router->get('/load/modul', function () {
});
$router->get('/load/config', function () {
});

$router->post('/auth/create_user', ['middleware' => 'cors', 'uses' => 'AuthController@create_auth_user']);
$router->post('/auth/update_user', ['middleware' => 'cors', 'uses' => 'AuthController@update_auth_user']);
$router->post('/auth/create_admin', ['middleware' => 'cors', 'uses' => 'AuthController@create_auth_admin']);
$router->post('/upload_berkas', ['middleware' => 'cors', 'uses' => 'BerkasMahasiswaController@upload_berkas']);
$router->get('/detail_berkas', ['middleware' => 'cors', 'uses' => 'BerkasMahasiswaController@detail_berkas']);
// $router->post('/mahasiswa', 'AuthController@create_auth_user');

$router->get('/auth/to_panel', ['middleware' => 'cors', 'uses' => 'RoleController@to_panel']);
$router->post('/auth/to_menu', ['middleware' => 'cors', 'uses' => 'RoleController@to_menu']);
$router->get('/utility/{type}', ['middleware' => 'cors', 'uses' => 'UtilityController@get_data']);
$router->post('/cek_query', ['middleware' => 'cors', 'uses' => 'UtilityController@cek_query']);


$router->get('/tahun_ajar_kampus', ['middleware' => 'cors', 'uses' => 'PmbAdminController@tahun_ajar_kampus']);

$router->post('/tahun_ajar_kampus_aktif', ['middleware' => 'cors', 'uses' => 'PmbAdminController@tahun_ajar_kampus_aktif']);




$router->post('/tambah_tahun_ajar_kampus', ['middleware' => 'cors', 'uses' => 'PmbAdminController@tambah_tahun_ajar_kampus']);
$router->post('/edit_tahun_ajar_kampus', ['middleware' => 'cors', 'uses' => 'PmbAdminController@edit_tahun_ajar_kampus']);
$router->post('/pmb/admin/pmb_jadwal', ['middleware' => 'cors', 'uses' => 'PmbAdminController@pmb_jadwal']);
$router->post('/pmb/admin/tambah_pmb_jadwal', ['middleware' => 'cors', 'uses' => 'PmbAdminController@tambah_pmb_jadwal']);
$router->post('/pmb/admin/edit_pmb_jadwal', ['middleware' => 'cors', 'uses' => 'PmbAdminController@edit_pmb_jadwal']);





//mahasiswa
$router->post('/pmb/mhs', ['middleware' => 'cors', 'uses' => 'MahasiswaController@index']);
$router->get('/detail_mhs', ['middleware' => 'cors', 'uses' => 'MahasiswaController@detail_mhs']);
$router->post('/pmb/mhs_hapus', ['middleware' => 'cors', 'uses' => 'MahasiswaController@mhs_hapus']);
$router->post('/pmb/update_status_pmb', ['middleware' => 'cors', 'uses' => 'MahasiswaController@update_status_pmb']);


$router->get('/pmb/mhs_tidak_lulus', ['middleware' => 'cors', 'uses' => 'MahasiswaController@mhs_tidak_lulus']);
$router->get('/pmb/mhs_prodi_pilihan', ['middleware' => 'cors', 'uses' => 'MahasiswaController@mhs_prodi_pilihan']);
$router->post('/pmb/mhs/simpan_pilihan', ['middleware' => 'cors', 'uses' => 'MahasiswaController@simpan_pilihan']);
//pmb



//pmb admin

$router->get('/pmb/admin/kelas_aktif', ['middleware' => 'cors', 'uses' => 'PmbAdminController@kelas_aktif']);
$router->get('/pmb/admin/kelas', ['middleware' => 'cors', 'uses' => 'PmbAdminController@kelas']);
$router->post('/pmb/admin/kelas_edit', ['middleware' => 'cors', 'uses' => 'PmbAdminController@kelas_edit']);


$router->get('/pmb/admin/dashboard', ['middleware' => 'cors', 'uses' => 'PmbAdminController@dashboard']);
$router->get('/pmb/admin/master_kampus_prodi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@master_kampus_prodi']);

$router->get('/pmb/admin/m_kampus_prodi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@m_kampus_prodi']);


$router->post('/pmb/admin/master_kampus_prodi_tambah', ['middleware' => 'cors', 'uses' => 'PmbAdminController@master_kampus_prodi_tambah']);
$router->post('/pmb/admin/master_kampus_prodi_edit', ['middleware' => 'cors', 'uses' => 'PmbAdminController@master_kampus_prodi_edit']);
$router->post('/pmb/admin/master_kampus_prodi_hapus', ['middleware' => 'cors', 'uses' => 'PmbAdminController@master_kampus_prodi_hapus']);
$router->post('/pmb/admin/master_kampus_visi_misi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@master_kampus_visi_misi']);
$router->post('/pmb/admin/master_kampus_propektus_kampus', ['middleware' => 'cors', 'uses' => 'PmbAdminController@master_kampus_propektus_kampus']);
$router->get('/pmb/admin/list_transaksi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@list_transaksi']);
$router->post('/pmb/admin/simulasi_biaya', ['middleware' => 'cors', 'uses' => 'PmbAdminController@simulasi_biaya']);
$router->post('/pmb/admin/gelombang', ['middleware' => 'cors', 'uses' => 'PmbAdminController@gelombang']);
$router->post('/pmb/admin/edit_gelombang', ['middleware' => 'cors', 'uses' => 'PmbAdminController@edit_gelombang']);
$router->post('/pmb/admin/tambah_gelombang', ['middleware' => 'cors', 'uses' => 'PmbAdminController@tambah_gelombang']);
$router->get('/pmb/admin/m_kampus_visi_misi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@m_kampus_visi_misi']);



$router->post('/pmb/admin/update_status_berkas', ['middleware' => 'cors', 'uses' => 'PmbAdminController@update_status_berkas']);
$router->get('/pmb/admin/list_berkas', ['middleware' => 'cors', 'uses' => 'PmbAdminController@list_berkas']);
$router->post('/pmb/admin/detail_mhs_edit', ['middleware' => 'cors', 'uses' => 'PmbAdminController@detail_mhs_edit']);
$router->post('/pmb/admin/update_mahasiswa_riwayat_pribadi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@update_mahasiswa_riwayat_pribadi']);
$router->post('/pmb/admin/update_mahasiswa_orangtua', ['middleware' => 'cors', 'uses' => 'PmbAdminController@update_mahasiswa_orangtua']);
$router->post('/pmb/admin/detail_berkas_admin', ['middleware' => 'cors', 'uses' => 'PmbAdminController@detail_berkas_admin']);
$router->post('/pmb/admin/detail_berkas', ['middleware' => 'cors', 'uses' => 'PmbAdminController@detail_berkas']);

$router->post('/pmb/admin/kartu_ujian', ['middleware' => 'cors', 'uses' => 'PmbAdminController@kartu_ujian']);
$router->post('/pmb/admin/cek_bayar', ['middleware' => 'cors', 'uses' => 'PmbAdminController@cek_bayar']);
$router->post('/pmb/admin/cek_lulus', ['middleware' => 'cors', 'uses' => 'PmbUserController@cek_lulus']);


$router->get('/pmb/admin/list_program_studi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@list_program_studi']);
$router->post('/pmb/admin/tambah_program_studi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@tambah_program_studi']);
$router->post('/pmb/admin/edit_program_studi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@edit_program_studi']);
$router->post('/pmb/admin/hapus_program_studi', ['middleware' => 'cors', 'uses' => 'PmbAdminController@hapus_program_studi']);









//pmb user
$router->post('/pmb/user/simulasi_biaya_user', ['middleware' => 'cors', 'uses' => 'PmbUserController@simulasi_biaya_user']);
$router->get('/pmb/tahapan', ['middleware' => 'cors', 'uses' => 'PmbUserController@tahapan']);
$router->get('/pmb/jadwal_uji_seleksi', ['middleware' => 'cors', 'uses' => 'PmbUserController@jadwal_uji_seleksi']);




//payment
$router->post('/payment', ['middleware' => 'cors', 'uses' => 'PaymentController@buat_payment']);
$router->post('/detail_payment', ['middleware' => 'cors', 'uses' => 'PaymentController@detail_payment']);
$router->post('/riwayat_payment', ['middleware' => 'cors', 'uses' => 'PaymentController@riwayat_payment']);
$router->post('/riwayat_pembayaran', ['middleware' => 'cors', 'uses' => 'PaymentController@riwayat_pembayaran']);
$router->post('/batal_payment', ['middleware' => 'cors', 'uses' => 'PaymentController@batal_payment']);

$router->post('/bayar_payment', ['middleware' => 'cors', 'uses' => 'PaymentController@bayar_payment']);


$router->post('/invoice', ['middleware' => 'cors', 'uses' => 'PaymentController@invoice']);
$router->post('/invoice_admin', ['middleware' => 'cors', 'uses' => 'PaymentController@invoice_admin']);


$router->post('/metode_pembayaran', ['middleware' => 'cors', 'uses' => 'PaymentController@metode_pembayaran']);

//tagihanMahasiswa
$router->post('/edit_penentuan_tarif', ['middleware' => 'cors', 'uses' => 'TagihanMahasiswaController@edit_penentuan_tarif']);

//keuangan admin
$router->get('/keuangan/admin/list_jenis_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_jenis_tagihan']);
$router->post('/keuangan/admin/jenis_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@jenis_tagihan']);

$router->post('/keuangan/admin/list_detail_jenis_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_detail_jenis_tagihan']);


$router->post('/keuangan/admin/tambah_jenis_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@tambah_jenis_tagihan']);
$router->post('/keuangan/admin/edit_jenis_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@edit_jenis_tagihan']);

$router->get('/keuangan/admin/list_penentuan_tarif', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_penentuan_tarif']);
$router->post('/keuangan/admin/buat_tarif_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@buat_tarif_tagihan']);
$router->post('/keuangan/admin/list_edit_buat_tarif', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_edit_buat_tarif']);
$router->post('/keuangan/admin/aksi_edit_buat_tarif', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@aksi_edit_buat_tarif']);
$router->post('/keuangan/admin/tampil_edit_buat_tarif', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@tampil_edit_buat_tarif']);
$router->post('/keuangan/admin/simpan_edit_penentuan_tarif', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@simpan_edit_penentuan_tarif']);


$router->get('/keuangan/admin/list_generate_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_generate_tagihan']);

$router->post('/keuangan/admin/generate_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@generate_tagihan']);

$router->post('/keuangan/admin/detail_generate_tagihan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@detail_generate_tagihan']);


$router->get('/keuangan/admin/list_aturan_akademik', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_aturan_akademik']);
$router->post('/keuangan/admin/tambah_aturan_akademik', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@tambah_aturan_akademik']);
$router->post('/keuangan/admin/edit_aturan_akademik', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@edit_aturan_akademik']);
$router->post('/keuangan/admin/hapus_aturan_akademik', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@hapus_aturan_akademik']);
$router->get('/keuangan/admin/list_tagihan_mahasiswa', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_tagihan_mahasiswa']);
$router->post('/keuangan/admin/detail_tagihan_mahasiswa', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@detail_tagihan_mahasiswa']);
$router->post('/keuangan/admin/detail_tagihan_mahasiswa_dispensasi', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@detail_tagihan_mahasiswa_dispensasi']);
$router->post('/keuangan/admin/tambah_mahasiswa_dispensasi', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@tambah_mahasiswa_dispensasi']);
$router->post('/keuangan/admin/hapus_mahasiswa_dispensasi', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@hapus_mahasiswa_dispensasi']);


$router->get('/keuangan/admin/list_potongan_biaya', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_potongan_biaya']);
$router->post('/keuangan/admin/tambah_potongan_biaya', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@tambah_potongan_biaya']);
$router->post('/keuangan/admin/edit_potongan_biaya', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@edit_potongan_biaya']);
$router->post('/keuangan/admin/hapus_potongan_biaya', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@hapus_potongan_biaya']);

$router->get('/keuangan/admin/potongan_kampus', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@potongan_kampus']);
$router->post('/keuangan/admin/pembiayaan_potongan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@pembiayaan_potongan']);
$router->post('/keuangan/admin/list_potongan_kampus', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_potongan_kampus']);
$router->post('/keuangan/admin/simpan_pengajuan_potongan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@simpan_pengajuan_potongan']);
$router->post('/keuangan/admin/update_tagihan_mahasiswa', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@update_tagihan_mahasiswa']);
$router->get('/keuangan/admin/list_penerima_potongan', ['middleware' => 'cors', 'uses' => 'KeuanganAdminController@list_penerima_potongan']);


$router->get('/siakad/admin/list_mahasiswa', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_mahasiswa']);
$router->post('/siakad/admin/tambah_mahasiswa', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_mahasiswa']);
$router->post('/siakad/admin/edit_mahasiswa', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_mahasiswa']);

$router->get('/siakad/admin/list_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_dosen']);
$router->post('/siakad/admin/tambah_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_dosen']);
$router->post('/siakad/admin/edit_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_dosen']);
$router->post('/siakad/admin/hapus_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_dosen']);

$router->get('/siakad/admin/list_penugasan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_penugasan_dosen']);
$router->post('/siakad/admin/tambah_penugasan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_penugasan_dosen']);
$router->post('/siakad/admin/edit_penugasan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_penugasan_dosen']);
$router->post('/siakad/admin/hapus_penugasan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_penugasan_dosen']);

$router->get('/siakad/admin/list_penugasan_perwalian', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_penugasan_perwalian']);
$router->post('/siakad/admin/tambah_penugasan_perwalian', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_penugasan_perwalian']);
$router->post('/siakad/admin/edit_penugasan_perwalian', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_penugasan_perwalian']);
$router->post('/siakad/admin/hapus_penugasan_perwalian', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_penugasan_perwalian']);

$router->get('/siakad/admin/list_mahasiswa_belum_perwalian', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_mahasiswa_belum_perwalian']);



$router->get('/siakad/admin/list_fakultas', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_fakultas']);
$router->post('/siakad/admin/tambah_fakultas', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_fakultas']);
$router->post('/siakad/admin/edit_fakultas', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_fakultas']);
$router->post('/siakad/admin/hapus_fakultas', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_fakultas']);

$router->get('/siakad/admin/list_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_program_studi']);
$router->post('/siakad/admin/tambah_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_program_studi']);
$router->post('/siakad/admin/edit_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_program_studi']);
$router->post('/siakad/admin/hapus_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_program_studi']);

$router->get('/siakad/admin/perkuliahan_list_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@perkuliahan_list_program_studi']);
$router->post('/siakad/admin/perkuliahan_edit_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@perkuliahan_edit_program_studi']);
$router->get('/siakad/admin/detail_perkuliahan_program_studi', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@detail_perkuliahan_program_studi']);


$router->get('/siakad/admin/list_matakuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_matakuliah']);
$router->post('/siakad/admin/tambah_matakuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_matakuliah']);
$router->post('/siakad/admin/edit_matakuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_matakuliah']);
$router->post('/siakad/admin/hapus_matakuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_matakuliah']);

$router->get('/siakad/admin/list_ruangan', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_ruangan']);
$router->post('/siakad/admin/tambah_ruangan', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_ruangan']);
$router->post('/siakad/admin/edit_ruangan', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_ruangan']);
$router->post('/siakad/admin/hapus_ruangan', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_ruangan']);

$router->get('/siakad/admin/list_grade', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_grade']);
$router->post('/siakad/admin/tambah_grade', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_grade']);
$router->post('/siakad/admin/edit_grade', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_grade']);
$router->post('/siakad/admin/hapus_grade', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_grade']);

$router->get('/siakad/admin/list_jadwal_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_jadwal_kuliah']);
$router->post('/siakad/admin/tambah_jadwal_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_jadwal_kuliah']);
$router->post('/siakad/admin/edit_jadwal_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_jadwal_kuliah']);
$router->post('/siakad/admin/hapus_jadwal_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_jadwal_kuliah']);

$router->get('/siakad/admin/list_peserta_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_peserta_kuliah']);
$router->post('/siakad/admin/tambah_peserta_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_peserta_kuliah']);
$router->post('/siakad/admin/edit_peserta_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_peserta_kuliah']);
$router->post('/siakad/admin/hapus_peserta_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_peserta_kuliah']);

$router->get('/siakad/admin/list_peminatan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_peminatan_dosen']);
$router->post('/siakad/admin/tambah_peminatan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_peminatan_dosen']);
$router->post('/siakad/admin/edit_peminatan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@edit_peminatan_dosen']);
$router->post('/siakad/admin/hapus_peminatan_dosen', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_peminatan_dosen']);

$router->get('/siakad/admin/list_kurikulum', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_kurikulum']);
$router->post('/siakad/admin/tambah_kurikulum', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@tambah_kurikulum']);
$router->post('/siakad/admin/ubah_kurikulum', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@ubah_kurikulum']);
$router->post('/siakad/admin/hapus_kurikulum', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@hapus_kurikulum']);
$router->get('/siakad/admin/jenis_mata_kuliah', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@jenis_mata_kuliah']);

$router->get('/siakad/admin/list_kelas', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_kelas']);
$router->get('/siakad/admin/list_semester', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@list_semester']);



$router->post('/siakad/admin/update_akte', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@update_akte']);
$router->post('/siakad/admin/update_informasi_pt', ['middleware' => 'cors', 'uses' => 'SiakadAdminController@update_informasi_pt']);







$router->get('/siakad/user/tagihan_mahasiswa', ['middleware' => 'cors', 'uses' => 'SiakadUserController@tagihan_mahasiswa']);

$router->post('/siakad/user/bayar_tagihan', ['middleware' => 'cors', 'uses' => 'SiakadUserController@bayar_tagihan']);
$router->post('/siakad/user/update_status_tagihan', ['middleware' => 'cors', 'uses' => 'SiakadUserController@update_status_tagihan']);

$router->get('/siakad/user/riwayat_payment', ['middleware' => 'cors', 'uses' => 'SiakadUserController@riwayat_payment']);
$router->post('/siakad/user/batal_payment', ['middleware' => 'cors', 'uses' => 'SiakadUserController@batal_payment']);
$router->get('/siakad/user/riwayat_pembayaran', ['middleware' => 'cors', 'uses' => 'SiakadUserController@riwayat_pembayaran']);
$router->post('/siakad/user/bayar_payment', ['middleware' => 'cors', 'uses' => 'SiakadUserController@bayar_payment']);
