<?php

namespace App\Http\Controllers;

// use App\Jobs\SubmitEmailJob;
// use App\Traits\utility;
// use Exception;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenInvalidException;
// use Tymon\JWTAuth\Facades\JWTAuth;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Str;
// use Tymon\JWTAuth\Facades\JWTFactory;

use App\Mail\FormEmail;
use App\Models\Jurusan;
use App\Models\KomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\MasterKampus;
use App\Models\MasterKomponen;
use App\Models\Role;
use App\Models\Dosen;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\utility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class SiakadAdminController extends Controller
{
    protected $jwt;
    use utility;


    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }
    public function list_mahasiswa(Request $req)
    {
        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');
            $daris = $req->get('dari');
            $hinggas = $req->get('hingga');
            $pmb_statuss = $req->get('pmb_status');
            $data_statuss = $req->get('data_status');
            $nims = $req->get('nim');
            $id_tahun_ajar = $req->get('id_tahun_ajar');
            $kode_jurusans = $req->kode_jurusan;
            $kelases = $req->kelas;



            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }



            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }

            if ($nims) {
                $nim = $nims;
            } else {
                $nim = '';
            }

            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }

            if ($kelases) {
                $kelas = $kelases;
            } else {
                $kelas = '';
            }


            // jurusan
            // id_tahun_ajar
            // nim
            // kelas


            $mhs = \DB::table('mahasiswa')
                ->join('auth', 'mahasiswa.id_auth', '=', 'auth.id')
                ->where('auth.id_kampus', $id_kampus)
                ->where('auth.akses', 'pmb')
                ->orderBy('mahasiswa.created_at', 'desc')
                ->where('pmb_status', 'lulus');


            if ($search) {
                $mhs = $mhs->where(function ($mhs) use ($search) {
                    $mhs->where('nama', 'LIKE', '%' . $search . '%')
                        ->orWhere('nim', $search);
                })->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa'], 'page', $page);
            } else if ($id_tahun_ajar) {
                $mhs = $mhs->where('id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa'], 'page', $page);
            } else if ($kode_jurusan) {
                $mhs = $mhs->where('jurusan1', $kode_jurusan)->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa'], 'page', $page);
            } else if ($kelas) {
                $mhs = $mhs->where('kelas', $kelas)->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa'], 'page', $page);
            } else {
                $mhs = $mhs->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa'], 'page', $page);
            }










            $dari = $mhs->currentPage();
            $hingga = $mhs->lastPage();
            $totaldata = $mhs->total();
            $totalhalaman = $mhs->count();
            $data = $mhs->items();

            error_reporting(0);

            foreach ($data as $d) {

                $email = DB::table('mahasiswa')
                    ->join('auth', 'auth.id', '=', 'mahasiswa.id_auth')
                    ->where('mahasiswa.id', $d->id_mahasiswa)
                    ->select('auth.email')->first();

                $data2[] = array(
                    'id_mahasiswa'            => $d->id_mahasiswa,
                    'id_auth'       => $d->id_auth,
                    'nama'          => $d->nama,
                    'jenis_kelamin' => $d->jenis_kelamin,
                    'tanggal_lahir' => $d->tanggal_lahir,
                    'tempat_lahir'  => $d->tempat_lahir,
                    'nama_ibu'      => $d->nama_ibu,
                    'agama'         => $d->agama,
                    'nama_agama' => $this->nama_agama($d->agama),
                    'kewarganegaraan' => $d->kewarganegaraan,
                    'nama_kewarganegaraan' => $this->nama_kewarganegaraan($d->kewarganegaraan),
                    'id_alat_transport' => $d->id_alat_transport,
                    'nama_alat_transport' => $this->transportasi($d->id_alat_transport),
                    'nik' =>  $d->nik,
                    'nisn' =>  $d->nisn,
                    'npwp' =>  $d->npwp,
                    'jalan' => $d->jalan,
                    'telp' => $d->telp,
                    'dusun' =>  $d->dusun,
                    'rt' =>  $d->rt,
                    'rw' =>  $d->rw,
                    'hp' =>  $d->hp,
                    'tanggal_create_at' => $d->created_at,
                    'kode_provinsi' => $d->provinsi,
                    'nama_provinsi' => $this->nama_wilayah($d->provinsi),
                    'kode_kota' => $d->kota,
                    'nama_kota' => $this->nama_wilayah($d->kota),
                    'kode_kelurahan' =>  $d->kelurahan,
                    'nama_kelurahan' =>  $this->nama_wilayah($d->kelurahan),
                    'kode_pos' =>  $d->kode_pos,
                    'penerima_kps' =>  $d->penerima_kps,
                    'kode_kecamatan' =>  $d->kecamatan,
                    'nama_kecamatan' => $this->nama_wilayah($d->kecamatan),
                    'jenis_tinggal' =>  $d->jenis_tinggal,
                    'nama_jenis_tinggal' => $this->nama_jenis_tinggal($d->jenis_tinggal),
                    'nik_ayah' =>  $d->nik_ayah,
                    'nama_ayah' =>  $d->nama_ayah,
                    'tanggal_lahir_ayah' =>  $d->tanggal_lahir_ayah,
                    'id_jenj_didik_ayah' =>  $d->id_jenj_didik_ayah,
                    'nama_jenj_didik_ayah' => $this->nama_jenjang_didik($d->id_jenj_didik_ayah),
                    'id_pekerjaan_ayah' =>  $d->id_pekerjaan_ayah,
                    'nama_pekerjaan_ayah' => $this->nama_pekerjaan($d->id_pekerjaan_ayah),
                    'id_penghasilan_ayah' =>  $d->id_penghasilan_ayah,
                    'nama_penghasilan_ayah' => $this->nama_penghasilan($d->id_penghasilan_ayah),
                    'nik_ibu' =>  $d->nik_ibu,
                    'tanggal_lahir_ibu' =>  $d->tanggal_lahir_ibu,
                    'id_jenj_didik_ibu' =>  $d->id_jenj_didik_ibu,
                    'nama_jenj_didik_ibu' => $this->nama_jenjang_didik($d->id_jenj_didik_ibu),
                    'id_pekerjaan_ibu' => $d->id_pekerjaan_ibu,
                    'nama_pekerjaan_ibu' => $this->nama_pekerjaan($d->id_pekerjaan_ibu),
                    'id_penghasilan_ibu' => $d->id_penghasilan_ibu,
                    'nama_penghasilan_ibu' => $this->nama_penghasilan($d->id_penghasilan_ibu),
                    'nama_wali' => $d->nama_wali,
                    'id_jenj_didik_wali' => $d->id_jenj_didik_wali,
                    'nama_jenj_didik_wali' => $this->nama_jenjang_didik($d->id_jenj_didik_wali),
                    'id_pekerjaan_wali' => $d->id_pekerjaan_wali,
                    'nama_pekerjaan_wali' => $this->nama_pekerjaan($d->id_pekerjaan_wali),
                    'id_penghasilan_wali' => $d->id_penghasilan_wali,
                    'nama_penghasilan_wali' => $this->nama_penghasilan($d->id_penghasilan_wali),
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at,
                    'pmb_no_pendaftaran' => $d->pmb_no_pendaftaran,
                    'asal_sekolah' => $d->asal_sekolah,
                    'jurusan1' => $d->jurusan1,
                    'nama_jurusan1' => $this->nama_prodi($d->jurusan1),
                    'jurusan2' => $d->jurusan2,
                    'nama_jurusan2' => $this->nama_prodi($d->jurusan2),
                    'jurusan3' => $d->jurusan3,
                    'nama_jurusan3' => $this->nama_prodi($d->jurusan3),
                    'tanggal_lahir_wali' => $d->tanggal_lahir_wali,
                    'id_gelombang' => $d->id_gelombang,
                    'nama_gelombang' => $this->nama_gelombang($d->id_gelombang),
                    'email' => $email->email,
                    // 'password' => $d->password,
                    'id_kampus' => $d->id_kampus,
                    'nama_lengkap' => $d->nama_lengkap,
                    'foto' =>  $d->foto,
                    'status' => $d->status,
                    'pmb_status' => $d->pmb_status,
                    // 'email_verified_at' =>  $d->email_verified_at,
                    // 'remember_token' => $d->remember_token,
                    'akses' => $d->akses,
                    'id_tahun_ajar' => $d->id_tahun_ajar,
                    'tahun_ajar' => $this->nama_tahun_ajar($d->id_tahun_ajar),
                    'sks' => null,
                    'mutu' => null,
                    'ipk' => null,
                    'id_kelompok_jenjang' => $d->id_kelompok_jenjang,
                    'id_kelas' => $d->id_kelas,
                    'nama_kelas' => $this->nama_kelas($d->id_kelas),
                    'tanggal_awal_masuk' => $d->tgl_awal_masuk,
                    'jalur_masuk' => $d->jalur_masuk,
                    'nama_jalur_masuk' => $this->jenis_pendaftaran($d->jalur_masuk),
                    'nim' => $d->nim,
                    'pendidikan_terakhir' => $d->pendidikan_terakhir,
                    'nama_pendidikan_terakhir' => $this->nama_jenjang_didik($d->pendidikan_terakhir),
                    'jenis_masuk' => $d->id_jenis_masuk,
                    'nama_jenis_masuk' => $this->nama_jalur_masuk($d->id_jenis_masuk),
                    'biaya_awal' => $d->pembiayaan_awal,
                    'status_akademik' => $d->status_akademik,
                    'baru_atau_pindahan' => $d->baru_atau_pindahan,
                    'id_semester' => $d->semester,
                    'nama_semester' => $this->nama_semester($d->semester)
                    // 'tanggal_masuk' => $d->tanggal_masuk,
                    // 'tanggal_keluar' => $d->tanggal_keluar,
                );
            }

            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data2));

            // echo json_encode($this->res_insert("fatal"));

        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    public function tambah_mahasiswa(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                // email
                // nama_lengkap
                // jenis kelamin
                // tanggal_lahir
                // tempat_lahir
                // nama_ibu
                // agama
                // kewarganegaraan
                // nik
                // nisn
                // npwp
                // jalan
                // telp
                // dusun
                // rt
                // rw 
                // hp
                // kelurhan
                // kode_pos
                // penerima_kps
                // kecamatan
                // jenis_tinggal
                // id_alat_transport
                // nik_ayah
                // nama_ayah
                // tanggal_lahir_ayah


                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;
                $rand = 123456;
                $user = new User();
                $user->password = sha1(md5($rand));
                $user->id_kampus = $id_kampus;
                $user->akses = "pmb";
                $user->email = $request->email;
                $user->nama_lengkap = $request->nama_lengkap;
                $user->save();

                $auth_role = new UserRole();
                $auth_role->id_auth = $user->id;
                $auth_role->id_role = 3;
                $auth_role->save();

                // echo $auth_role

                $mahasiswa                      = new Mahasiswa();
                $mahasiswa->id_auth             = $user->id;
                $mahasiswa->nama                = $request->nama_lengkap;
                $mahasiswa->jenis_kelamin       = $request->jenis_kelamin;
                $mahasiswa->tanggal_lahir       = $request->tanggal_lahir;
                $mahasiswa->tempat_lahir        = $request->tempat_lahir;
                $mahasiswa->nama_ibu            = $request->nama_ibu;
                $mahasiswa->agama               = $request->agama;
                $mahasiswa->kewarganegaraan     = $request->kewarganegaraan;
                $mahasiswa->nik                 = $request->nik;
                $mahasiswa->nisn                = $request->nisn;
                $mahasiswa->npwp                = $request->npwp;
                $mahasiswa->jalan               = $request->jalan;
                $mahasiswa->telp                = $request->telp;
                $mahasiswa->dusun               = $request->dusun;
                $mahasiswa->rt                  = $request->rt;
                $mahasiswa->rw                  = $request->rw;
                $mahasiswa->hp                  = $request->hp;
                $mahasiswa->kelurahan           = $request->kelurahan;
                $mahasiswa->kode_pos            = $request->kode_pos;
                $mahasiswa->penerima_kps        = $request->penerima_kps;
                $mahasiswa->kecamatan           = $request->kecamatan;
                $mahasiswa->jenis_tinggal       = $request->jenis_tinggal;
                $mahasiswa->id_alat_transport   = $request->id_alat_transportasi;
                $mahasiswa->nik_ayah            = $request->nik_ayah;
                $mahasiswa->nama_ayah           = $request->nama_ayah;
                $mahasiswa->tanggal_lahir_ayah  = $request->tanggal_lahir_ayah;
                $mahasiswa->id_jenj_didik_ayah  = $request->id_jenj_didik_ayah;
                $mahasiswa->id_pekerjaan_ayah   = $request->id_pekerjaan_ayah;
                $mahasiswa->id_penghasilan_ayah = $request->id_penghasilan_ayah;
                $mahasiswa->nik_ibu             = $request->nik_ibu;
                $mahasiswa->tanggal_lahir_ibu   = $request->tanggal_lahir_ibu;
                $mahasiswa->id_jenj_didik_ibu   = $request->id_jenj_didik_ibu;
                $mahasiswa->id_pekerjaan_ibu    = $request->id_pekerjaan_ibu;
                $mahasiswa->id_penghasilan_ibu  = $request->id_penghasilan_ibu;
                $mahasiswa->nama_wali           = $request->nama_wali;
                $mahasiswa->id_jenj_didik_wali  = $request->id_jenj_didik_wali;
                $mahasiswa->id_pekerjaan_wali   = $request->id_pekerjaan_wali;
                $mahasiswa->id_penghasilan_wali = $request->id_penghasilan_wali;
                $mahasiswa->created_at          = date('Y-m-d H:i:s');
                $mahasiswa->updated_at          = date('Y-m-d H:i:s');
                $mahasiswa->pmb_no_pendaftaran = $this->buat_nomor_pmb_pendaftaran($payload->id_kampus, $request->jurusan);
                $mahasiswa->asal_sekolah        = $request->asal_sekolah;
                $mahasiswa->jurusan1        = $request->jurusan1;
                $mahasiswa->jurusan2        = $request->jurusan2;
                $mahasiswa->jurusan3        = $request->jurusan3;
                $mahasiswa->tanggal_lahir_wali        = $request->tanggal_lahir_wali;
                $mahasiswa->id_gelombang        = $request->id_gelombang;
                $mahasiswa->status        = 1;
                $mahasiswa->tgl_pmb_batal        = $request->tgl_pmb_batal;
                $mahasiswa->pmb_status        = $request->pmb_status;
                $mahasiswa->pmb_status_date        = $request->pmb_status_date;
                $mahasiswa->pmb_jadwal_ujian        = $request->pmb_jadwal_ujian;
                $mahasiswa->jalur_masuk        = $request->jalur_masuk;
                $mahasiswa->kebutuhan_khusus        = $request->kebutuhan_khusus;
                $mahasiswa->provinsi        = $request->provinsi;
                $mahasiswa->kota        = $request->kota;
                $mahasiswa->semester        = $request->semester;
                $mahasiswa->id_tahun_ajar        = $request->id_tahun_ajar;
                $mahasiswa->nim        = $request->nim;
                $mahasiswa->id_tahun_ajar_masuk        = $request->id_tahun_ajar;
                $mahasiswa->pendidikan_terakhir = $request->pendidikan_terakhir;
                $mahasiswa->id_kelompok_jenjang = $request->id_kelompok_jenjang;
                $mahasiswa->id_kelas        = $request->kelas;
                $mahasiswa->id_jenis_masuk        = $request->id_jenis_masuk;
                $mahasiswa->baru_atau_pindahan = $request->baru_atau_pindahan;
                $mahasiswa->tgl_awal_masuk = $request->tanggal_awal_masuk;
                $mahasiswa->pembiayaan_awal = $request->pembiayaan_awal;
                $mahasiswa->status_akademik = $request->status_akademik;
                // $mahasiswa->email = $request->email;
                $mahasiswa->pmb_status = 'lulus';

                // $mahasiswa->pmb_no_pendaftaran  = $this->buat_nomor_pmb_pendaftaran($kampus->id, $request->jurusan);
                // $mahasiswa->asal_sekolah        = $request->asal_sekolah;
                // $mahasiswa->jurusan1            = $request->jurusan;


                // $mahasiswa->nama            = $request->nama_lengkap;
                // $mahasiswa->jenis_kelamin   = "";
                // $mahasiswa->tanggal_lahir   = null;
                // $mahasiswa->tempat_lahir    = "";
                // $mahasiswa->nama_ibu        = "";
                // $mahasiswa->agama           = "";
                // $mahasiswa->kewarganegaraan = "";
                // $mahasiswa->nik             = "";
                // $mahasiswa->nisn            = "";
                // $mahasiswa->telp            = $request->nomor_wa;
                // $mahasiswa->kelas            = $request->kelas;
                // $mahasiswa->kelurahan       = "";
                // $mahasiswa->penerima_kps    = "";
                // $mahasiswa->kecamatan       = "";
                $mahasiswa->save();
            });
            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    public function edit_mahasiswa(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {


                $id_mahasiswa = $request->id_mahasiswa;
                $id_auth_mahasiswa = DB::table('mahasiswa')->select('id_auth')->where('id', $id_mahasiswa)->first();
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;
                // $rand = 123456;
                // $user = User::find($id_auth_mahasiswa->id_auth);
                // $user->password = sha1(md5($rand));
                // $user->id_kampus = $id_kampus;
                // $user->akses = "pmb";
                // $user->email = $request->email;
                // $user->nama_lengkap = $request->nama_lengkap;
                // $user->save();

                // $auth_role = new UserRole;
                // $auth_role->id_auth = $user->id;
                // $auth_role->id_role = 3;
                // $auth_role->save();
                $auth_role = DB::table('auth_role')->where('id_auth', $id_auth_mahasiswa->id_auth)->update([
                    'id_role' => 3
                ]);

                if ($request->type == "formulir") {
                    $mahasiswa                      = Mahasiswa::find($id_mahasiswa);
                    $mahasiswa->id_auth             = $id_auth_mahasiswa->id_auth;
                    $mahasiswa->nama                = $request->has('nama_lengkap') ? $request->nama_lengkap : $mahasiswa->nama;
                    $mahasiswa->jenis_kelamin       = $request->has('jenis_kelamin') ? $request->jenis_kelamin : $mahasiswa->jenis_kelamin;
                    $mahasiswa->tanggal_lahir       = $request->has('tanggal_lahir') ? $request->tanggal_lahir : $mahasiswa->tanggal_lahir;
                    $mahasiswa->tempat_lahir        = $request->has('tempat_lahir') ? $request->tempat_lahir : $mahasiswa->tempat_lahir;
                    $mahasiswa->nama_ibu            = $request->has('nama_ibu') ? $request->nama_ibu : $mahasiswa->nama_ibu;
                    $mahasiswa->agama               = $request->has('agama') ? $request->agama : $mahasiswa->agama;
                    $mahasiswa->kewarganegaraan     = $request->has('kewarganegaraan') ? $request->kewarganegaraan : $mahasiswa->kewarganegaraan;
                    $mahasiswa->nik                 = $request->has('nik') ? $request->nik : $mahasiswa->nik;
                    $mahasiswa->nisn                = $request->has('nisn') ? $request->nisn : $mahasiswa->nisn;
                    $mahasiswa->npwp                = $request->has('npwp') ? $request->npwp : $mahasiswa->npwp;
                    $mahasiswa->jalan               = $request->has('jalan') ? $request->jalan : $mahasiswa->jalan;
                    $mahasiswa->telp                = $request->has('telp') ? $request->telp : $mahasiswa->telp;
                    $mahasiswa->dusun               = $request->has('dusun') ? $request->dusun : $mahasiswa->dusun;
                    $mahasiswa->rt                  = $request->has('rt') ? $request->rt : $mahasiswa->rt;
                    $mahasiswa->rw                  = $request->has('rw') ? $request->rw : $mahasiswa->rw;
                    $mahasiswa->hp                  = $request->has('hp') ? $request->hp : $mahasiswa->hp;
                    $mahasiswa->kelurahan           = $request->has('kelurahan') ? $request->kelurahan : $mahasiswa->kelurahan;
                    $mahasiswa->kode_pos            = $request->has('kode_pos') ? $request->kode_pos : $mahasiswa->kode_pos;
                    $mahasiswa->penerima_kps        = $request->has('penerima_kps') ? $request->penerima_kps : $mahasiswa->penerima_kps;
                    $mahasiswa->kecamatan           = $request->has('kecamatan') ? $request->kecamatan : $mahasiswa->kecamatan;
                    $mahasiswa->jenis_tinggal       = $request->has('jenis_tinggal') ? $request->jenis_tinggal : $mahasiswa->jenis_tinggal;
                    $mahasiswa->id_alat_transport   = $request->has('id_alat_transport') ? $request->id_alat_transport : $mahasiswa->id_alat_transport;
                    $mahasiswa->nik_ayah            = $request->has('nik_ayah') ? $request->nik_ayah : $mahasiswa->nik_ayah;
                    $mahasiswa->nama_ayah           = $request->has('nama_ayah') ? $request->nama_ayah : $mahasiswa->nama_ayah;
                    $mahasiswa->tanggal_lahir_ayah  = $request->has('tanggal_lahir_ayah') ? $request->tanggal_lahir_ayah : $mahasiswa->tanggal_lahir_ayah;
                    $mahasiswa->id_jenj_didik_ayah  = $request->has('id_jenj_didik_ayah') ? $request->id_jenj_didik_ayah : $mahasiswa->id_jenj_didik_ayah;
                    $mahasiswa->id_pekerjaan_ayah   = $request->has('id_pekerjaan_ayah') ? $request->id_pekerjaan_ayah : $mahasiswa->id_pekerjaan_ayah;
                    $mahasiswa->id_penghasilan_ayah = $request->has('id_penghasilan_ayah') ? $request->id_penghasilan_ayah : $mahasiswa->id_penghasilan_ayah;
                    $mahasiswa->nik_ibu             = $request->has('nik_ibu') ? $request->nik_ibu : $mahasiswa->nik_ibu;
                    $mahasiswa->tanggal_lahir_ibu   = $request->has('tanggal_lahir_ibu') ? $request->tanggal_lahir_ibu : $mahasiswa->tanggal_lahir_ibu;
                    $mahasiswa->id_jenj_didik_ibu   = $request->has('id_jenj_didik_ibu') ? $request->id_jenj_didik_ibu : $mahasiswa->id_jenj_didik_ibu;
                    $mahasiswa->id_pekerjaan_ibu    = $request->has('id_pekerjaan_ibu') ? $request->id_pekerjaan_ibu : $mahasiswa->id_pekerjaan_ibu;
                    $mahasiswa->id_penghasilan_ibu  = $request->has('id_penghasilan_ibu') ? $request->id_penghasilan_ibu : $mahasiswa->id_penghasilan_ibu;
                    $mahasiswa->nama_wali           = $request->has('nama_wali') ? $request->nama_wali : $mahasiswa->nama_wali;
                    $mahasiswa->id_jenj_didik_wali  = $request->has('id_jenj_didik_wali') ? $request->id_jenj_didik_wali : $mahasiswa->id_jenj_didik_wali;
                    $mahasiswa->id_pekerjaan_wali   = $request->has('id_pekerjaan_wali') ? $request->id_pekerjaan_wali : $mahasiswa->id_pekerjaan_wali;
                    $mahasiswa->id_penghasilan_wali = $request->has('id_penghasilan_wali') ? $request->id_penghasilan_wali : $mahasiswa->id_penghasilan_wali;
                    $mahasiswa->created_at          = date('Y-m-d H:i:s');
                    $mahasiswa->updated_at          = date('Y-m-d H:i:s');
                    $mahasiswa->pmb_no_pendaftaran = $this->buat_nomor_pmb_pendaftaran($payload->id_kampus, $request->jurusan1);
                    $mahasiswa->asal_sekolah        = $request->has('asal_sekolah') ? $request->asal_sekolah : $mahasiswa->asal_sekolah;
                    $mahasiswa->jurusan1        = $request->has('jurusan1') ? $request->jurusan1 : $mahasiswa->jurusan1;
                    $mahasiswa->jurusan2        = $request->has('jurusan2') ? $request->jurusan2 : $mahasiswa->jurusan2;
                    $mahasiswa->jurusan3        = $request->has('jurusan3') ? $request->jurusan3 : $mahasiswa->jurusan3;
                    $mahasiswa->tanggal_lahir_wali        = $request->has('tanggal_lahir_wali') ? $request->tanggal_lahir_wali : $mahasiswa->tanggal_lahir_wali;
                    $mahasiswa->id_gelombang        = $request->has('id_gelombang') ? $request->id_gelombang : $mahasiswa->id_gelombang;
                    $mahasiswa->status        = 1;
                    $mahasiswa->tgl_pmb_batal        = $request->has('tgl_pmb_batal') ? $request->tgl_pmb_batal : $mahasiswa->tgl_pmb_batal;
                    $mahasiswa->pmb_status        = $request->has('pmb_status') ? $request->pmb_status : $mahasiswa->pmb_status;
                    $mahasiswa->pmb_status_date        = $request->has('pmb_status_date') ? $request->pmb_status_date : $mahasiswa->pmb_status_date;
                    $mahasiswa->pmb_jadwal_ujian        = $request->has('pmb_jadwal_ujian') ? $request->pmb_jadwal_ujian : $mahasiswa->pmb_jadwal_ujian;
                    $mahasiswa->jalur_masuk        = $request->has('jalur_masuk') ? $request->jalur_masuk : $mahasiswa->jalur_masuk;
                    $mahasiswa->kebutuhan_khusus        = $request->has('kebutuhan_khusus') ? $request->kebutuhan_khusus : $mahasiswa->kebutuhan_khusus;
                    $mahasiswa->provinsi        = $request->has('provinsi') ? $request->provinsi : $mahasiswa->provinsi;
                    $mahasiswa->kota        = $request->has('kota') ? $request->kota : $mahasiswa->kota;
                    $mahasiswa->semester        = $request->has('semester') ? $request->semester : $mahasiswa->semester;
                    $mahasiswa->id_tahun_ajar        = $request->has('id_tahun_ajar') ? $request->id_tahun_ajar : $mahasiswa->id_tahun_ajar;
                    $mahasiswa->nim        = $request->has('nim') ? $request->nim : $mahasiswa->nim;
                    // $mahasiswa->id_tahun_ajar_masuk        = $request->id_tahun_ajar;
                    $mahasiswa->pendidikan_terakhir = $request->has('pendidikan_terakhir') ? $request->pendidikan_terakhir : $mahasiswa->pendidikan_terakhir;
                    $mahasiswa->id_kelompok_jenjang = $request->has('id_kelompok_jenjang') ? $request->id_kelompok_jenjang : $mahasiswa->id_kelompok_jenjang;
                    $mahasiswa->id_kelas        = $request->has('id_kelas') ? $request->id_kelas : $mahasiswa->id_kelas;
                    $mahasiswa->id_jenis_masuk        = $request->has('id_jenis_masuk') ? $request->id_jenis_masuk : $mahasiswa->id_jenis_masuk;
                    $mahasiswa->baru_atau_pindahan = $request->has('baru_atau_pindahan') ? $request->baru_atau_pindahan : $mahasiswa->baru_atau_pindahan;
                    $mahasiswa->tgl_awal_masuk = $request->has('tgl_awal_masuk') ? $request->tgl_awal_masuk : $mahasiswa->tgl_awal_masuk;
                    $mahasiswa->pembiayaan_awal = $request->has('pembiayaan_awal') ? $request->pembiayaan_awal : $mahasiswa->pembiayaan_awal;
                    $mahasiswa->status_akademik = $request->has('status_akademik') ? $request->status_akademik : $mahasiswa->status_akademik;
                    // $mahasiswa->email = $request->has('email') ? $request->email : $mahasiswa->email;
                    $mahasiswa->pmb_status = $request->has('pmb_status') ? $request->pmb_status : $mahasiswa->pmb_status;
                    $mahasiswa->save();
                } else {
                    if ($request->type == "data_mahasiswa") {
                        $validator = $this->validate($request, [
                            'nama_lengkap'          => 'required',
                            'jenis_kelamin' => 'required',
                            'tanggal_lahir' => 'required',
                            'tempat_lahir'  => 'required',
                            'nama_ibu'      => 'required',
                            'agama'         => 'required',
                            'nik'         => 'required',
                            'nisn'         => 'required',
                            'nama_ayah'         => 'required',
                            // 'email'         => 'required',
                        ]);
                        $mahasiswa                      = Mahasiswa::find($id_mahasiswa);
                        $mahasiswa->id_auth             = $id_auth_mahasiswa->id_auth;
                        $mahasiswa->nama                = $request->has('nama') ? $request->nama_lengkap : $mahasiswa->nama;
                        $mahasiswa->jenis_kelamin       = $request->has('jenis_kelamin') ? $request->jenis_kelamin : $mahasiswa->jenis_kelamin;
                        $mahasiswa->tanggal_lahir       = $request->has('tanggal_lahir') ? $request->tanggal_lahir : $mahasiswa->tanggal_lahir;
                        $mahasiswa->tempat_lahir        = $request->has('tempat_lahir') ? $request->tempat_lahir : $mahasiswa->tempat_lahir;
                        $mahasiswa->agama               = $request->has('agama') ? $request->agama : $mahasiswa->agama;
                        $mahasiswa->nik                 = $request->has('nik') ? $request->nik : $mahasiswa->nik;
                        $mahasiswa->nisn                = $request->has('nisn') ? $request->nisn : $mahasiswa->nisn;
                        $mahasiswa->nama_ibu            = $request->has('nama_ibu') ? $request->nama_ibu : $mahasiswa->nama_ibu;
                        $mahasiswa->nama_ayah           = $request->has('nama_ayah') ? $request->nama_ayah : $mahasiswa->nama_ayah;
                        // $mahasiswa->email               = $request->has('email') ? $request->email : $mahasiswa->email;
                        $mahasiswa->save();
                    } else if ($request->type == "info_alamat_mahasiswa") {
                        $validator = $this->validate($request, [
                            'kewarganegaraan'          => 'required',
                            'jenis_tinggal' => 'required',
                            'provinsi' => 'required',
                            'kota'  => 'required',
                            'kecamatan'      => 'required',
                            'rt'         => 'required',
                            'rw'         => 'required',
                            'kelurahan'         => 'required',
                            'kode_pos'         => 'required',
                            'id_alat_transport'         => 'required',
                        ]);
                        $mahasiswa                      = Mahasiswa::find($id_mahasiswa);
                        $mahasiswa->kewarganegaraan     = $request->has('kewarganegaraan') ? $request->kewarganegaraan : $mahasiswa->kewarganegaraan;
                        $mahasiswa->jenis_tinggal       = $request->has('jenis_tinggal') ? $request->jenis_tinggal : $mahasiswa->jenis_tinggal;
                        $mahasiswa->provinsi        = $request->has('provinsi') ? $request->provinsi : $mahasiswa->provinsi;
                        $mahasiswa->kota        = $request->has('kota') ? $request->kota : $mahasiswa->kota;
                        $mahasiswa->kecamatan           = $request->has('kecamatan') ? $request->kecamatan : $mahasiswa->kecamatan;
                        $mahasiswa->rt                  = $request->has('rt') ? $request->rt : $mahasiswa->rt;
                        $mahasiswa->rw                  = $request->has('rw') ? $request->rw : $mahasiswa->rw;
                        $mahasiswa->kelurahan           = $request->has('kelurahan') ? $request->kelurahan : $mahasiswa->kelurahan;
                        $mahasiswa->kode_pos            = $request->has('kode_pos') ? $request->kode_pos : $mahasiswa->kode_pos;
                        $mahasiswa->id_alat_transport   = $request->has('id_alat_transport') ? $request->id_alat_transport : $mahasiswa->id_alat_transport;
                        $mahasiswa->save();
                    } else if ($request->type == "info_ortu_mahasiswa") {
                        $validator = $this->validate($request, [
                            'nik_ayah'          => 'required',
                            'nama_ayah' => 'required',
                            'tanggal_lahir_ayah' => 'required',
                            'id_jenj_didik_ayah'  => 'required',
                            'id_pekerjaan_ayah'      => 'required',
                            'id_penghasilan_ayah'         => 'required',
                            'nik_ibu'         => 'required',
                            'nama_ibu'         => 'required',
                            'tanggal_lahir_ibu'         => 'required',
                            'id_jenj_didik_ibu'         => 'required',
                            'id_pekerjaan_ibu'         => 'required',
                            'id_penghasilan_ibu'         => 'required',
                        ]);
                        $mahasiswa                      = Mahasiswa::find($id_mahasiswa);

                        $mahasiswa->nik_ayah            = $request->has('nik_ayah') ? $request->nik_ayah : $mahasiswa->nik_ayah;
                        $mahasiswa->nama_ayah           = $request->has('nama_ayah') ? $request->nama_ayah : $mahasiswa->nama_ayah;
                        $mahasiswa->tanggal_lahir_ayah  = $request->has('tanggal_lahir_ayah') ? $request->tanggal_lahir_ayah : $mahasiswa->tanggal_lahir_ayah;
                        $mahasiswa->id_jenj_didik_ayah  = $request->has('id_jenj_didik_ayah') ? $request->id_jenj_didik_ayah : $mahasiswa->id_jenj_didik_ayah;
                        $mahasiswa->id_pekerjaan_ayah   = $request->has('id_pekerjaan_ayah') ? $request->id_pekerjaan_ayah : $mahasiswa->id_pekerjaan_ayah;
                        $mahasiswa->id_penghasilan_ayah = $request->has('id_penghasilan_ayah') ? $request->id_penghasilan_ayah : $mahasiswa->id_penghasilan_ayah;
                        $mahasiswa->nik_ibu             = $request->has('nik_ibu') ? $request->nik_ibu : $mahasiswa->nik_ibu;
                        $mahasiswa->nama_ibu             = $request->has('nama_ibu') ? $request->nama_ibu : $mahasiswa->nama_ibu;
                        $mahasiswa->tanggal_lahir_ibu   = $request->has('tanggal_lahir_ibu') ? $request->tanggal_lahir_ibu : $mahasiswa->tanggal_lahir_ibu;
                        $mahasiswa->id_jenj_didik_ibu   = $request->has('id_jenj_didik_ibu') ? $request->id_jenj_didik_ibu : $mahasiswa->id_jenj_didik_ibu;
                        $mahasiswa->id_pekerjaan_ibu    = $request->has('id_pekerjaan_ibu') ? $request->id_pekerjaan_ibu : $mahasiswa->id_pekerjaan_ibu;
                        $mahasiswa->id_penghasilan_ibu  = $request->has('id_penghasilan_ibu') ? $request->id_penghasilan_ibu : $mahasiswa->id_penghasilan_ibu;
                        $mahasiswa->pmb_status          = $request->has('pmb_status') ? $request->pmb_status : $mahasiswa->pmb_status;


                        // $mahasiswa->nik_ayah            = $request->nik_ayah;
                        // $mahasiswa->nama_ayah           = $request->nama_ayah;
                        // $mahasiswa->tanggal_lahir_ayah  = $request->tanggal_lahir_ayah;
                        // $mahasiswa->id_jenj_didik_ayah  = $request->id_jenj_didik_ayah;
                        // $mahasiswa->id_pekerjaan_ayah   = $request->id_pekerjaan_ayah;
                        // $mahasiswa->id_penghasilan_ayah = $request->id_penghasilan_ayah;
                        // $mahasiswa->nik_ibu             = $request->nik_ibu;
                        // $mahasiswa->nama_ibu             = $request->nama_ibu;
                        // $mahasiswa->tanggal_lahir_ibu   = $request->tanggal_lahir_ibu;
                        // $mahasiswa->id_jenj_didik_ibu   = $request->id_jenj_didik_ibu;
                        // $mahasiswa->id_pekerjaan_ibu    = $request->id_pekerjaan_ibu;
                        // $mahasiswa->id_penghasilan_ibu  = $request->id_penghasilan_ibu;
                        $mahasiswa->save();
                    } else if ($request->type == "info_wali_mahasiswa") {
                        $validator = $this->validate($request, [
                            'nama_wali'          => 'required',
                            'id_jenj_didik_wali' => 'required',
                            'id_pekerjaan_wali' => 'required',
                            'id_penghasilan_wali'  => 'required',
                        ]);
                        $mahasiswa                      = Mahasiswa::find($id_mahasiswa);
                        $mahasiswa->nama_wali           = $request->nama_wali;
                        $mahasiswa->id_jenj_didik_wali  = $request->id_jenj_didik_wali;
                        $mahasiswa->id_pekerjaan_wali   = $request->id_pekerjaan_wali;
                        $mahasiswa->id_penghasilan_wali = $request->id_penghasilan_wali;
                        $mahasiswa->save();
                    } else if ($request->type == "info_khusus_mahasiswa") {
                        $validator = $this->validate($request, [
                            'kebutuhan_khusus'          => 'required',
                        ]);
                        $mahasiswa                      = Mahasiswa::find($id_mahasiswa);
                        $mahasiswa->kebutuhan_khusus     =  $request->kebutuhan_khusus;
                        $mahasiswa->save();
                    }
                }
            });
            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function list_dosen(Request $req)
    {
        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');
            $daris = $req->get('dari');
            $hinggas = $req->get('hingga');
            $pmb_statuss = $req->get('pmb_status');
            $data_statuss = $req->get('data_status');
            $nims = $req->get('nim');
            $id_tahun_ajar = $req->get('id_tahun_ajar');
            $kode_jurusans = $req->kode_jurusan;
            $kelases = $req->kelas;

            if ($data_statuss == 1) {
                $data_status = 1;
            } elseif ($data_statuss == 0) {
                $data_status = 0;
            } else {
                $data_status = 0;
            }

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            if ($pmb_statuss) {
                $pmb_status = [$pmb_statuss];
            } else {
                $pmb_status = ['lulus', 'tidak_lulus', ''];
            }

            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }

            if ($nims) {
                $nim = $nims;
            } else {
                $nim = '';
            }

            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }

            if ($kelases) {
                $kelas = $kelases;
            } else {
                $kelas = '';
            }


            // jurusan
            // id_tahun_ajar
            // nim
            // kelas


            $dosens = \DB::table('m_dosen')
                ->join('auth', 'm_dosen.id_auth', '=', 'auth.id')
                ->where('auth.id_kampus', $id_kampus)
                ->where('auth.akses', 'dosen');




            if ($search) {
                $dosen = $dosens->where(function ($dosen) use ($search) {
                    $dosen->where('nama_dosen', 'LIKE', '%' . $search . '%')
                        ->orWhere('nidn', $search);
                })->paginate($limit, ['m_dosen.*'], 'page', $page);
            } else {
                $dosen = $dosens->paginate($limit, ['m_dosen.*'], 'page', $page);
            }

            // else if ($id_tahun_ajar) {
            //     $dosen = $dosen->where('id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*'], 'page', $page);
            // } else if ($kode_jurusan) {
            //     $dosen = $dosen->where('jurusan1', $kode_jurusan)->paginate($limit, ['*'], 'page', $page);
            // } else if ($kelas) {
            //     $dosen = $dosen->where('kelas', $kelas)->paginate($limit, ['*'], 'page', $page);
            // } else {
            //     $dosen = $dosen->paginate($limit, ['*'], 'page', $page);
            // }

            $dari = $dosen->currentPage();
            $hingga = $dosen->lastPage();
            $totaldata = $dosen->total();
            $totalhalaman = $dosen->count();
            $data = $dosen->items();


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));

            // echo json_encode($this->res_insert("fatal"));

        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function tambah_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $check = User::where(["email" => $request->email, "id_kampus" => $payload->id_kampus])->get()->count();
            if ($check) {
                return response()->json($this->res_insert("unique"));
            }

            DB::transaction(function () use ($payload, $request) {
                // email
                // nama_lengkap
                // jenis kelamin
                // tanggal_lahir
                // tempat_lahir
                // nama_ibu
                // agama
                // kewarganegaraan
                // nik
                // nisn
                // npwp
                // jalan
                // telp
                // dusun
                // rt
                // rw 
                // hp
                // kelurhan
                // kode_pos
                // penerima_kps
                // kecamatan
                // jenis_tinggal
                // id_alat_transport
                // nik_ayah
                // nama_ayah
                // tanggal_lahir_ayah

                // $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;
                $rand = 123456;
                $user = new User();
                $user->password = sha1(md5($rand));
                $user->id_kampus = $id_kampus;
                $user->akses = "dosen";
                $user->email =  $request->email;
                $user->nama_lengkap = $request->nama_lengkap;
                $user->save();



                $auth_role = new UserRole();
                $auth_role->id_auth = $user->id;
                $auth_role->id_role = 4;
                $auth_role->save();


                $dosen                      = new Dosen();
                $dosen->id_auth             = $user->id;
                $dosen->nama_dosen                  = $request->nama_lengkap;
                $dosen->kode_dosen = $request->kode_dosen;
                $dosen->nidn = $request->nidn;
                $dosen->kode_lengkap = $request->kode_lengkap;
                $dosen->nama_cetak = $request->nama_cetak;
                $dosen->gelar_depan                 = $request->gelar_depan;
                $dosen->gelar_belakang              = $request->agama;
                $dosen->pendidikan_terakhir         = $request->pendidikan_terakhir;
                $dosen->jabatan_akademik            = $request->jabatan_akademik;
                $dosen->ikatan_kerja_dosen          = $request->ikatan_kerja_dosen;
                $dosen->tempat_lahir                = $request->tempat_lahir;
                $dosen->tgl_lahir                   = $request->tgl_lahir;
                $dosen->jenis_kelamin               = $request->jenis_kelamin;
                $dosen->agama                       = $request->agama;
                $dosen->status_perkawinan           = $request->status_perkawinan;
                $dosen->provinsi                    = $request->provinsi;
                $dosen->kabupaten                   = $request->kabupaten;
                $dosen->kecamatan                   = $request->kecamatan;
                $dosen->kelurahan                   = $request->kelurahan;
                $dosen->kode_pos                    = $request->kode_pos;
                $dosen->aktif_mengajar              = $request->aktif_mengajar;
                $dosen->id_kampus                   = $payload->id_kampus;
                $dosen->alamat_surat_menyurat       = $request->alamat_surat_menyurat;
                $dosen->telepon                     = $request->telepon;
                $dosen->hp                          = $request->hp;
                $dosen->email                       = $request->email;
                $dosen->perusahaan                  = $request->perusahaan;
                $dosen->alamat_perusahaan           = $request->alamat_perusahaan;
                $dosen->telp_perusahaan             = $request->telp_perusahaan;
                $dosen->kabupaten_perusahaan        = $request->kabupaten_perusahaan;
                $dosen->id_auth                     = $user->id;
                $dosen->updated_at                  = date('Y-m-d H:i:s');




                // $dosen->id_kampus                   = $id_kampus;
                // $dosen->nama_cetak                  = $request->nama_cetak;
                // $dosen->ikatan_kerja_dosen          = $request->ikatan_kerja_dosen;
                //$dosen->kabupaten               = $request->kabupaten; 
                // $dosen->nidn                        = $request->nidn;

                $dosen->save();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function edit_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {



                //$dosen                      = new Dosen();

                $dosen                    = Dosen::where('id_auth', $request->id_auth)->first();
                //$dosen->id_auth             = $user->id;
                $dosen->nama_dosen        = $request->nama_lengkap;
                $dosen->kode_dosen = $request->kode_dosen;
                $dosen->nidn = $request->nidn;
                $dosen->kode_lengkap = $request->kode_lengkap;
                $dosen->nama_cetak = $request->nama_cetak;
                $dosen->gelar_depan                 = $request->gelar_depan;
                $dosen->gelar_belakang              = $request->gelar_belakang;
                $dosen->pendidikan_terakhir         = $request->pendidikan_terakhir;
                $dosen->jabatan_akademik            = $request->jabatan_akademik;
                $dosen->ikatan_kerja_dosen          = $request->ikatan_kerja_dosen;
                $dosen->tempat_lahir                = $request->tempat_lahir;
                $dosen->tgl_lahir                   = $request->tgl_lahir;
                $dosen->jenis_kelamin               = $request->jenis_kelamin;
                $dosen->agama                       = $request->agama;
                $dosen->status_perkawinan           = $request->status_perkawinan;
                $dosen->provinsi                    = $request->provinsi;
                $dosen->kabupaten                   = $request->kabupaten;
                $dosen->kecamatan                   = $request->kecamatan;
                $dosen->kelurahan                   = $request->kelurahan;
                $dosen->kode_pos                    = $request->kode_pos;
                $dosen->id_kampus                   = $payload->id_kampus;
                $dosen->updated_at                  = date('Y-m-d H:i:s');

                $dosen->save();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {


                $id_dosen = $request->id_dosen;


                DB::table('m_dosen')->where('id_dosen', $id_dosen)->delete();
            });
            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function list_penugasan_dosen(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');



            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $searchs = $req->get('search');
            $limits = $req->get('limit');
            $types = $req->get('type');
            $statuss = $req->get('status');

            $id_tahun_ajars = $req->get('id_tahun_ajar');
            $kode_jurusans = $req->kode_jurusan;
            $id_penugasan_dosens = $req->get('id_penugasan_dosen');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }

            if ($id_tahun_ajars) {
                $id_tahun_ajar = $id_tahun_ajars;
            } else {
                $id_tahun_ajar = '';
            }

            if ($id_penugasan_dosens) {
                $id_penugasan_dosen = $id_penugasan_dosens;
            } else {
                $id_penugasan_dosen = '';
            }

            if ($searchs) {
                $search = $searchs;
            } else {
                $search = '';
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }






            $dosens = \DB::table('penugasan_dosen')
                ->where('penugasan_dosen.id_kampus', $id_kampus);

            if ($id_penugasan_dosen) {
                $dosen = $dosens->where('id_penugasan_dosen', $id_penugasan_dosen)->paginate($limit, ['*'], 'page', $page);
            } else if ($id_tahun_ajar) {
                $dosen = $dosens->where('id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*'], 'page', $page);
            } else if ($kode_jurusan) {
                $dosen = $dosens->where('kode_jurusan', $kode_jurusan)->paginate($limit, ['*'], 'page', $page);
            } else if ($status == '1' || $status == '0') {
                if ($status == '1') {
                    $dosen = $dosens->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == '0') {
                    $dosen = $dosens->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $dosen = $dosens->paginate($limit, ['*'], 'page', $page);
            }


            foreach ($dosen as $a) {
                $data2[] = array(
                    'id_penugasan_dosen' => $a->id_penugasan_dosen,
                    'id_dosen' => $a->id_dosen,
                    'nama_dosen' => $this->nama_dosen($a->id_dosen),
                    'id_tahun_ajar' => $a->id_tahun_ajar,
                    'nama_tahun_ajar' => $this->nama_tahun_ajar($a->id_tahun_ajar),
                    'kode_jurusan' => $a->kode_jurusan,
                    'nama_jurusan' => $this->nama_prodi($a->kode_jurusan),
                    'no_surat_tugas' => $a->no_surat_tugas,
                    'tgl_surat_tugas' => $a->tgl_surat_tugas,
                    'tmt_surat_tugas' => $a->tmt_surat_tugas,
                    'status' => $a->status,
                    'id_kampus' => $a->id_kampus,
                    'crdt' => $a->crdt
                );
            }





            $dari = $dosen->currentPage();
            $hingga = $dosen->lastPage();
            $totaldata = $dosen->total();
            $totalhalaman = $dosen->count();
            $data = $data2;


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function tambah_penugasan_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;
                $id_dosen = $request->id_dosen;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $kode_jurusan = $request->kode_jurusan;
                $no_surat_tugas = $request->no_surat_tugas;
                $tgl_surat_tugas = $request->tgl_surat_tugas;
                $tmt_surat_tugas = $request->tmt_surat_tugas;
                $status = $request->status;

                DB::table('penugasan_dosen')->insert([
                    'id_dosen' => $id_dosen,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'kode_jurusan' => $kode_jurusan,
                    'no_surat_tugas' => $no_surat_tugas,
                    'tgl_surat_tugas' => $tgl_surat_tugas,
                    'tmt_surat_tugas' => $tmt_surat_tugas,
                    'status' => $status,
                    'crdt' => date('Y-m-d'),
                    'id_kampus' => $id_kampus
                ]);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function edit_penugasan_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;
                $dosen = DB::table('penugasan_dosen')->where('id_penugasan_dosen', $request->id_penugasan_dosen)->first();
                DB::table('penugasan_dosen')->where('id_penugasan_dosen', $request->id_penugasan_dosen)->update([
                    'id_dosen' => $request->has('id_dosen') ? $request->id_dosen : $dosen->id_dosen,
                    'id_tahun_ajar' => $request->has('id_tahun_ajar') ? $request->id_tahun_ajar : $dosen->id_tahun_ajar,
                    'kode_jurusan' => $request->has('kode_jurusan') ? $request->kode_jurusan : $dosen->kode_jurusan,
                    'no_surat_tugas' => $request->has('no_surat_tugas') ? $request->no_surat_tugas : $dosen->no_surat_tugas,
                    'tgl_surat_tugas' => $request->has('tgl_surat_tugas') ? $request->tgl_surat_tugas : $dosen->tgl_surat_tugas,
                    'tmt_surat_tugas' => $request->has('tmt_surat_tugas') ? $request->tmt_surat_tugas : $dosen->tmt_surat_tugas,
                    'status' => $request->has('status') ? $request->status : $dosen->status,
                    'crdt' => date('Y-m-d')
                ]);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_penugasan_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $id_penugasan_dosen = $request->id_penugasan_dosen;
                DB::table('penugasan_dosen')->where('id_penugasan_dosen', $id_penugasan_dosen)->delete();
                DB::table('penugasan_perwalian')->where('id_penugasan_dosen', $id_penugasan_dosen)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function tambah_penugasan_perwalian(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;


                $id_kelas = $request->id_kelas;
                $nim = explode(",", $request->nim);
                $nama_lengkap = explode(",", $request->nama_lengkap);
                $id_penugasan_dosen = $request->id_penugasan_dosen;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $kode_jurusan = $request->kode_jurusan;
                $status = (int) $request->status;




                if (is_array($id_kelas)) {
                    foreach ($id_kelas as $a) {
                        if ($a !== "") {
                            DB::table('penugasan_perwalian')->insert([
                                'id_kelas' => $a,
                                'id_penugasan_dosen' => $id_penugasan_dosen,
                                'id_kampus' => $id_kampus,
                                'status' => $status,
                                'crdt' => date('Y-m-d'),
                                'id_tahun_ajar' => $id_tahun_ajar,
                                'kode_jurusan' => $kode_jurusan,
                            ]);
                        }
                    }
                }

                if (is_array($nim) and is_array($nama_lengkap)) {
                    foreach ($nim as $key => $b) {
                        if ($b !== "" || $nama_lengkap[$key] !== "") {
                            DB::table('penugasan_perwalian')->insert([
                                'nim' => $b,
                                'nama_lengkap' => $nama_lengkap[$key],
                                'id_penugasan_dosen' => $id_penugasan_dosen,
                                'id_kampus' => $id_kampus,
                                'status' => $status,
                                'crdt' => date('Y-m-d'),
                                'id_tahun_ajar' => $id_tahun_ajar,
                                'kode_jurusan' => $kode_jurusan,
                            ]);
                        }
                    }
                }
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function list_penugasan_perwalian(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');



            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');
            $types = $req->get('type');
            $id_penugasan_dosens = $req->get('id_penugasan_dosen');


            $id_tahun_ajar = $req->get('id_tahun_ajar');
            $kode_jurusans = $req->kode_jurusan;


            if ($types) {
                $type = $types;
            } else {
                $type = '';
            }

            if ($id_penugasan_dosens) {
                $id_penugasan_dosen = $id_penugasan_dosens;
            } else {
                $id_penugasan_dosen = '';
            }

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }






            $dosen = \DB::table('penugasan_perwalian as a')
                ->join('penugasan_dosen', 'penugasan_dosen.id_penugasan_dosen', '=', 'a.id_penugasan_dosen')
                ->where('penugasan_dosen.id_penugasan_dosen', $id_penugasan_dosen)
                ->select('a.id_penugasan_perwalian', 'a.id_kelas', 'a.nim', 'a.nama_lengkap', 'a.id_penugasan_dosen', 'a.id_kampus', 'a.status', 'a.crdt', 'a.id_tahun_ajar', 'a.kode_jurusan')->paginate($limit, ['*'], 'page', $page);

            $arr[] = array(
                'id_tahun_ajar' => 'asd'
            );


            foreach ($dosen as $a) {
                // $data2[] = array(
                //     'id_penugasan_perwalian' => $a->id_penugasan_perwalian,
                //     'id_kelas' => $a->id_kelas,
                //     'nama_kelas' => $this->nama_kelas_abc($a->id_kelas),
                //     'nim' => $a->nim,
                //     'nama_lengkap' => $a->nama_lengkap,
                //     'id_penugasan_dosen' => $a->id_penugasan_dosen,
                //     'id_kampus' => $a->id_kampus,
                //     'status' => $a->status,
                //     'crdt' => $a->crdt,
                //     'id_tahun_ajar' => $a->id_tahun_ajar,
                //     'nama_tahun_ajar' => $this->nama_tahun_ajar($a->id_tahun_ajar),
                //     'kode_jurusan' => $a->kode_jurusan,
                //     'nama_jurusan' => $this->nama_prodi($a->kode_jurusan)
                // );
                $id_tahun_ajar = $a->id_tahun_ajar;

                if ($a->id_kelas == null) {
                    $mahasiswa[] = array(
                        'id_penugasan_perwalian' => $a->id_penugasan_perwalian,
                        'nama_kelas' => $this->nama_kelas_abc($a->id_kelas),
                        'nim' => $a->nim,
                        'nama_lengkap' => $a->nama_lengkap,
                        'id_penugasan_dosen' => $a->id_penugasan_dosen,
                        'id_kampus' => $a->id_kampus,
                        'status' => $a->status,
                        'crdt' => $a->crdt,
                        'id_tahun_ajar' => $a->id_tahun_ajar,
                        'nama_tahun_ajar' => $this->nama_tahun_ajar($a->id_tahun_ajar),
                        'kode_jurusan' => $a->kode_jurusan,
                        'nama_jurusan' => $this->nama_prodi($a->kode_jurusan)
                    );
                } else {
                    $kelas[] = array(
                        'id_penugasan_perwalian' => $a->id_penugasan_perwalian,
                        'id_kelas' => $a->id_kelas,
                        'nama_kelas' => $this->nama_kelas_abc($a->id_kelas),
                        'id_penugasan_dosen' => $a->id_penugasan_dosen,
                        'id_kampus' => $a->id_kampus,
                        'status' => $a->status,
                        'crdt' => $a->crdt,
                        'id_tahun_ajar' => $a->id_tahun_ajar,
                        'nama_tahun_ajar' => $this->nama_tahun_ajar($a->id_tahun_ajar),
                        'kode_jurusan' => $a->kode_jurusan,
                        'nama_jurusan' => $this->nama_prodi($a->kode_jurusan)
                    );
                }
            }

            $data2[] = array(
                'id_tahun_ajar' => $id_tahun_ajar,
                'nama_tahun_ajar' => $this->nama_tahun_ajar($id_tahun_ajar),
                'mahasiswa' => $mahasiswa,
                'kelas' => $kelas
            );






            $dari = $dosen->currentPage();
            $hingga = $dosen->lastPage();
            $totaldata = $dosen->total();
            $totalhalaman = $dosen->count();
            $data = $data2;


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_mahasiswa_belum_perwalian(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');



            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $mhs = \DB::table('mahasiswa')
                ->join('auth', 'mahasiswa.id_auth', '=', 'auth.id')
                ->join('penugasan_perwalian', 'penugasan_perwalian.id_kampus', '!=', 'auth.id_kampus')
                ->where('auth.id_kampus', $id_kampus)
                ->where('auth.akses', 'pmb')
                ->where('penugasan_perwalian.nim', '!=', 'mahasiswa.nim')
                ->orderBy('mahasiswa.created_at', 'desc')
                ->where('mahasiswa.pmb_status', 'lulus')
                ->select('mahasiswa.nama')
                ->get();


            dd($mhs);






            die;

            $arr[] = array(
                'id_tahun_ajar' => 'asd'
            );

            $dari = $dosen->currentPage();
            $hingga = $dosen->lastPage();
            $totaldata = $dosen->total();
            $totalhalaman = $dosen->count();
            $data = $data2;


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function edit_penugasan_perwalian(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $id_penugasan_perwalian = is_array($request->id_penugasan_perwalian) ? $request->id_penugasan_perwalian : explode(',', $request->id_penugasan_perwalian);
                $id_kelas = $request->id_kelas;
                $nim = explode(",", $request->nim);
                $nama_lengkap = explode(",", $request->nama_lengkap);
                $id_penugasan_dosen = $request->id_penugasan_dosen;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $kode_jurusan = $request->kode_jurusan;
                $status = $request->status;

                $tes = DB::table('penugasan_perwalian')->where('id_penugasan_dosen', $id_penugasan_dosen)->select('id_penugasan_perwalian')->get();
                foreach ($tes as $m => $x) {
                    if (isset($id_penugasan_perwalian[$m])) {
                        // echo $x->id_penugasan_perwalian
                    } else {
                        DB::table('penugasan_perwalian')->where('id_penugasan_dosen', $id_penugasan_dosen)->where('id_penugasan_perwalian', $x->id_penugasan_perwalian)->delete();
                    }
                }

                // die;

                if (is_array($id_kelas)) {
                    foreach ($id_kelas as $index => $a) {
                        if ($a !== "") {
                            DB::table('penugasan_perwalian')->where('id_penugasan_perwalian', $id_penugasan_perwalian[$index])->update([
                                'id_kelas' => $a,
                                // 'id_penugasan_dosen' => $id_penugasan_dosen,
                                'status' => $status,
                                'id_tahun_ajar' => $id_tahun_ajar,
                                'kode_jurusan' => $kode_jurusan,
                                'nim' => null,
                                'nama_lengkap' => null
                            ]);
                        }
                    }
                }

                if (is_array($nim) and is_array($nama_lengkap)) {
                    foreach ($nim as $key => $b) {
                        if ($b !== "" || $nama_lengkap[$key] !== "") {
                            DB::table('penugasan_perwalian')->where('id_penugasan_perwalian', $id_penugasan_perwalian[$key])->update([
                                'nim' => $b,
                                'nama_lengkap' => $nama_lengkap[$key],
                                // 'id_penugasan_dosen' => $id_penugasan_dosen,
                                'status' => $status,
                                'id_tahun_ajar' => $id_tahun_ajar,
                                'kode_jurusan' => $kode_jurusan,
                                'id_kelas' => null
                            ]);
                        }
                    }
                }
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function hapus_penugasan_perwalian(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $id_penugasan_perwalian = is_array($request->id_penugasan_perwalian) ? $request->id_penugasan_perwalian : explode(',', $request->id_penugasan_perwalian);

                foreach ($id_penugasan_perwalian as $a) {
                    DB::table('penugasan_perwalian')->where('id_penugasan_perwalian', $a)->update([
                        'status' => '0'
                    ]);
                }
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }



    function list_fakultas(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $search = $req->get('search');
                $limits = $req->get('limit');



                $id_tahun_ajar = $req->get('id_tahun_ajar');
                $kode_jurusans = $req->kode_jurusan;



                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }


                if ($kode_jurusans) {
                    $kode_jurusan = $kode_jurusans;
                } else {
                    $kode_jurusan = '';
                }






                $dosen = \DB::table('m_fakultas')->where('id_kampus', $id_kampus);



                if ($search) {
                    $dosen = $dosen->where(function ($dosen) use ($search) {
                        $dosen->where('nama_fakultas', 'LIKE', '%' . $search . '%')
                            ->orWhere('ketua', $search);
                    })->paginate($limit, ['*'], 'page', $page);
                } else {
                    $dosen = $dosen->paginate($limit, ['*'], 'page', $page);
                }





                $dari = $dosen->currentPage();
                $hingga = $dosen->lastPage();
                $totaldata = $dosen->total();
                $totalhalaman = $dosen->count();
                $data = $dosen->items();


                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function tambah_fakultas(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $kode_fakultas = $request->kode_fakultas;
                $kode_epsbed = $request->kode_epsbed;
                $nama_fakultas = $request->nama_fakultas;
                $ketua = $request->ketua;
                $status = $request->status;


                $data_simpan = array(
                    'kode_fakultas' => $kode_fakultas,
                    'kode_epsbed' => $kode_epsbed,
                    'nama_fakultas' => $nama_fakultas,
                    'ketua' => $ketua,
                    'status' => $status,
                    'id_kampus' => $id_kampus,
                    'crdt' => date('Y-m-d'),
                );

                DB::table('m_fakultas')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function edit_fakultas(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $kode_fakultas = $request->kode_fakultas;
                $kode_epsbed = $request->kode_epsbed;
                $nama_fakultas = $request->nama_fakultas;
                $ketua = $request->ketua;
                $status = $request->status;
                $id_fakultas = $request->id_fakultas;


                $data_simpan = array(
                    'kode_fakultas' => $kode_fakultas,
                    'kode_epsbed' => $kode_epsbed,
                    'nama_fakultas' => $nama_fakultas,
                    'ketua' => $ketua,
                    'status' => $status,
                    'id_kampus' => $id_kampus,
                    'crdt' => date('Y-m-d H:i:s'),
                );

                DB::table('m_fakultas')->where('id_fakultas', $id_fakultas)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_fakultas(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $id_fakultas = $request->id_fakultas;
                DB::table('m_fakultas')->where('id_fakultas', $id_fakultas)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function list_program_studi(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $search = $req->get('search');
                $limits = $req->get('limit');



                $id_fakultas = $req->get('id_fakultas');
                $kode_jurusans = $req->kode_jurusan;



                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }


                if ($kode_jurusans) {
                    $kode_jurusan = $kode_jurusans;
                } else {
                    $kode_jurusan = '';
                }






                $programstudi = \DB::table('m_program_studi')->where('id_kampus', $id_kampus);



                if ($search) {
                    $programstudi = $programstudi->where(function ($programstudi) use ($search) {
                        $programstudi->where('nama_prodi', 'LIKE', '%' . $search . '%')
                            ->orWhere('ka_prodi', $search);
                    })->paginate($limit, ['*'], 'page', $page);
                } else if ($id_fakultas) {
                    $programstudi = $programstudi->where('id_fakultas', $id_fakultas)->paginate($limit, ['*'], 'page', $page);
                } else if ($kode_jurusan) {
                    $programstudi = $programstudi->where('kode_jurusan', $kode_jurusan)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $programstudi = $programstudi->paginate($limit, ['*'], 'page', $page);
                }





                $dari = $programstudi->currentPage();
                $hingga = $programstudi->lastPage();
                $totaldata = $programstudi->total();
                $totalhalaman = $programstudi->count();
                $data = $programstudi->items();


                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function tambah_program_studi(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $id_fakultas = $request->id_fakultas;
                $nama_prodi = $request->nama_prodi;
                $kode_jurusan = $request->kode_jurusan;
                $kode_epsbed = $request->kode_epsbed;
                $id_jenjang = $request->id_jenjang;
                $ka_prodi = $request->ka_prodi;
                $kurilulum = $request->kurilulum;
                $kurilulum_lama = $request->kurilulum_lama;
                $tgl_ban_sk = $request->tgl_ban_sk;
                $akreditasi = $request->akreditasi;
                $no_sk_ban_pt = $request->no_sk_ban_pt;
                $sks_lulus = $request->sks_lulus;
                $batas_studi = $request->batas_studi;
                $status = $request->status;





                $data_simpan = array(
                    'id_fakultas' => $id_fakultas,
                    'nama_prodi' => $nama_prodi,
                    'kode_jurusan' => $kode_jurusan,
                    'kode_epsbed' => $kode_epsbed,
                    'id_jenjang' => $id_jenjang,
                    'ka_prodi' => $ka_prodi,
                    'id_kampus' => $id_kampus,
                    'kurilulum' => $kurilulum,
                    'kurilulum_lama' => $kurilulum_lama,
                    'tgl_ban_sk' => $tgl_ban_sk,
                    'akreditasi' => $akreditasi,
                    'no_sk_ban_pt' => $no_sk_ban_pt,
                    'sks_lulus' => $sks_lulus,
                    'batas_studi' => $batas_studi,
                    'status' => $status,
                    'crdt' => date('Y-m-d'),
                );

                DB::table('m_program_studi')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function edit_program_studi(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {


                $id_fakultas = $request->id_fakultas;
                $nama_prodi = $request->nama_prodi;
                $kode_jurusan = $request->kode_jurusan;
                $kode_epsbed = $request->kode_epsbed;
                $id_jenjang = $request->id_jenjang;
                $ka_prodi = $request->ka_prodi;
                $kurilulum = $request->kurilulum;
                $kurilulum_lama = $request->kurilulum_lama;
                $tgl_ban_sk = $request->tgl_ban_sk;
                $akreditasi = $request->akreditasi;
                $no_sk_ban_pt = $request->no_sk_ban_pt;
                $sks_lulus = $request->sks_lulus;
                $batas_studi = $request->batas_studi;
                $status = $request->status;
                $id_program_studi = $request->id_program_studi;





                $data_simpan = array(
                    'id_fakultas' => $id_fakultas,
                    'nama_prodi' => $nama_prodi,
                    'kode_jurusan' => $kode_jurusan,
                    'kode_epsbed' => $kode_epsbed,
                    'id_jenjang' => $id_jenjang,
                    'ka_prodi' => $ka_prodi,
                    'id_kampus' => $payload->id_kampus,
                    'kurilulum' => $kurilulum,
                    'kurilulum_lama' => $kurilulum_lama,
                    'tgl_ban_sk' => $tgl_ban_sk,
                    'akreditasi' => $akreditasi,
                    'no_sk_ban_pt' => $no_sk_ban_pt,
                    'sks_lulus' => $sks_lulus,
                    'batas_studi' => $batas_studi,
                    'status' => $status,
                    'crdt' => date('Y-m-d H:i:s'),
                );

                DB::table('m_program_studi')->where('id_program_studi', $id_program_studi)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_program_studi(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $id_program_studi = $request->id_program_studi;
                DB::table('m_program_studi')->where('id_program_studi', $id_program_studi)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function perkuliahan_list_program_studi(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $search = $req->get('search');
                $limits = $req->get('limit');



                $id_fakultas = $req->get('id_fakultas');
                $kode_jurusans = $req->kode_jurusan;



                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }


                if ($kode_jurusans) {
                    $kode_jurusan = $kode_jurusans;
                } else {
                    $kode_jurusan = '';
                }






                // 
                $programstudi = \DB::table('m_program_studi')->paginate($limit, ['*'], 'page', $page);





                foreach ($programstudi as $a) {
                    $jml_dosen = DB::table('m_matakuliah')
                        ->join('m_jadwal_kuliah', 'm_jadwal_kuliah.id_mata_kuliah', '=', 'm_matakuliah.id_matakuliah')
                        ->where('m_matakuliah.id_kampus', $id_kampus)
                        ->where('m_matakuliah.kode_jurusan', $a->kode)
                        ->select('m_jadwal_kuliah.id_mata_kuliah', 'm_jadwal_kuliah.id_dosen')
                        ->groupBy('m_jadwal_kuliah.id_mata_kuliah', 'm_jadwal_kuliah.id_dosen')
                        ->get();

                    $jml_mhs = DB::table('mahasiswa')
                        ->join('auth', 'auth.id', '=', 'mahasiswa.id_auth')
                        ->where('auth.id_kampus', $id_kampus)
                        ->where('mahasiswa.pmb_status', 'lulus')
                        ->where('mahasiswa.jurusan1', $a->kode)
                        ->get();

                    $data2[] = array(
                        'id_program_studi' => $a->id_program_studi,
                        'kode_prodi' => $a->kode,
                        'program_studi' => $a->nama_prodi,
                        'status' => $a->status,
                        'jenjang' => $this->just_jenjang_prodi($a->kode),
                        'jumlah_mahasiswa' => count($jml_mhs),
                        'jumlah_dosen' => count($jml_dosen)
                    );
                }


                $dari = $programstudi->currentPage();
                $hingga = $programstudi->lastPage();
                $totaldata = $programstudi->total();
                $totalhalaman = $programstudi->count();
                $data = $data2;


                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function detail_perkuliahan_program_studi(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $search = $req->get('search');
                $limits = $req->get('limit');



                $id_fakultas = $req->get('id_fakultas');
                $kode_jurusans = $req->kode_jurusan;
                $id_program_studis = $req->get('id_program_studi');



                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }


                if ($kode_jurusans) {
                    $kode_jurusan = $kode_jurusans;
                } else {
                    $kode_jurusan = '';
                }


                if ($id_program_studis) {
                    $id_program_studi = $id_program_studis;
                } else {
                    $id_program_studi = '';
                }






                // 
                $programstudis = \DB::table('m_program_studi')
                    ->where('id_kampus', $id_kampus);


                if ($id_program_studi) {
                    $programstudi = $programstudis->where('id_program_studi', $id_program_studi)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $programstudi = $programstudis->paginate($limit, ['*'], 'page', $page);
                }




                $dari = $programstudi->currentPage();
                $hingga = $programstudi->lastPage();
                $totaldata = $programstudi->total();
                $totalhalaman = $programstudi->count();
                $data =  $programstudi->items();


                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function edit_perkuliahan_program_studi(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                $pro_studi = DB::table('m_program_studi')->where('id_program_studi', $request->id_program_studi)->first();
                $data_simpan = array(
                    'akreditasi' => $req->has('akreditasi') ? $req->akreditasi : $pro_studi->akreditasi,
                    'status' => $req->has('status') ? $req->status : $pro_studi->status,
                    'ka_prodi' => $req->has('ka_prodi') ? $req->ka_prodi : $pro_studi->ka_prodi,
                );

                DB::table('m_program_studi')->where('id_program_studi', $req->id_program_studi)->update($data_simpan);
            });
            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function list_semester(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');



            $id_tahun_ajar = $req->get('id_tahun_ajar');
            $statuss = $req->status;



            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }


            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            if ($statuss) {
                $status = $statuss;
            } else {
                $status = '';
            }

            $semester = \DB::table('m_semester as s')
                ->join('tahun_ajar as t', 's.id_tahun_ajar', '=', 't.id')
                ->where('s.id_kampus', $id_kampus);


            if ($search) {
                $semester = $semester->where(function ($semester) use ($search) {
                    $semester->where('t.nama', 'LIKE', '%' . $search . '%')
                        ->orWhere('t.keterangan', $search);
                })->paginate($limit, ['*'], 'page', $page);
            } else if ($id_tahun_ajar) {
                $semester = $semester->where('id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*'], 'page', $page);
            } else if ($status) {
                $semester = $semester->where('status', $status)->paginate($limit, ['*'], 'page', $page);
            } else {
                $semester = $semester->paginate($limit, ['*'], 'page', $page);
            }





            $dari = $semester->currentPage();
            $hingga = $semester->lastPage();
            $totaldata = $semester->total();
            $totalhalaman = $semester->count();
            $data = $semester->items();


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function tambah_semester(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $status = $request->status;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $waktu_awal = $request->waktu_awal;
                $waktu_akhir = $request->waktu_akhir;


                $data_simpan = array(
                    'status' => $status,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'waktu_awal' => $waktu_awal,
                    'waktu_akhir' => $waktu_akhir,

                    'id_kampus' => $id_kampus,
                    'crdt' => date('Y-m-d'),
                );

                DB::table('m_semester')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function edit_semester(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {


                $status = $request->status;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $waktu_awal = $request->waktu_awal;
                $waktu_akhir = $request->waktu_akhir;
                $id_semester = $request->id_semester;


                $data_simpan = array(
                    'status' => $status,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'waktu_awal' => $waktu_awal,
                    'waktu_akhir' => $waktu_akhir,
                    'id_kampus' => $payload->id_kampus,
                );

                DB::table('m_semester')->where('id_semester', $id_semester)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_semester(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $id_semester = $request->id_semester;
                DB::table('m_semester')->where('id_semester', $id_semester)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function list_matakuliah(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');



            $id_tahun_ajar = $req->get('id_tahun_ajar');
            $statuss = $req->get('status');
            $kode_jurusans = $req->get('kode_jurusan');



            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }


            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }





            $matakuliah = \DB::table('m_matakuliah')->where('id_kampus', $id_kampus);



            if ($search) {
                $matakuliah = $matakuliah->where(function ($matakuliah) use ($search) {
                    $matakuliah->where('nama_matakuliah', 'LIKE', '%' . $search . '%');
                })->paginate($limit, ['*'], 'page', $page);
            } else if ($id_tahun_ajar) {
                $matakuliah = $matakuliah->where('id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*'], 'page', $page);
            } else if ($kode_jurusan) {
                $matakuliah = $matakuliah->where('kode_jurusan', $kode_jurusan)->paginate($limit, ['*'], 'page', $page);
            } else if ($status == '1' || $status == '0') {
                $matakuliah = $matakuliah->where('status', $status)->paginate($limit, ['*'], 'page', $page);
            } else {
                $matakuliah = $matakuliah->paginate($limit, ['*'], 'page', $page);
            }

            foreach ($matakuliah as $value) {
                $data2[] = array(
                    'id_mata_kuliah' => $value->id_matakuliah,
                    'kode_mata_kuliah' => $value->kode_matakuliah,
                    'nama_mata_kuliah' => $value->nama_matakuliah,
                    'jenis_mata_kuliah' => $value->jenis_matakuliah,
                    'kode_jurusan' => $value->kode_jurusan,
                    'nama_jurusan' => $this->nama_prodi($value->kode_jurusan),
                    'bobot_tatap_muka' => $value->bobot_tatap_muka,
                    'bobot_pratikum' => $value->bobot_pratikum,
                    'bobot_praktek_lapangan' => $value->bobot_praktek_lapangan,
                    'bobot_simulasi' => $value->bobot_simulasi,
                    'id_semester' => $value->id_semester,
                    'semester' => $this->nama_semester($value->id_semester),
                    'bobot_mata_kuliah' => $value->bobot_matakuliah,
                    'metode_pembelajaran' => $value->metode_pembelajaran,
                    'tgl_mulai_efektif' => $value->tgl_mulai_efektif,
                    'tgl_akhir_efektif' => $value->tgl_akhir_efektif,
                    'status' => $value->status,
                    'crdt' => $value->crdt,
                    'id_kampus' => $value->id_kampus
                );
            }



            $dari = $matakuliah->currentPage();
            $hingga = $matakuliah->lastPage();
            $totaldata = $matakuliah->total();
            $totalhalaman = $matakuliah->count();
            $data = $data2;


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function tambah_matakuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $kode_matakuliah = $request->kode_mata_kuliah;
                $nama_matakuliah = $request->nama_mata_kuliah;
                $jenis_mata_kuliah = $request->jenis_mata_kuliah;
                $kode_jurusan = $request->kode_jurusan;
                $bobot_tatap_muka = $request->bobot_tatap_muka;
                $bobot_praktikum = $request->bobot_praktikum;
                $bobot_praktek_lapangan = $request->bobot_praktek_lapangan;
                $bobot_simulasi = $request->bobot_simulasi;
                $metode_pembelajaran = $request->metode_pembelajaran;
                $tgl_mulai_efektif = $request->tgl_mulai_efektif;
                $tgl_akhir_efektif = $request->tgl_akhir_efektif;
                $status = $request->status;
                $bobot_mata_kuliah = $request->bobot_mata_kuliah;
                $id_semester = $request->id_semester;
                $crdt = date('Y-m-d');

                $data_simpan = array(
                    'kode_matakuliah' => $kode_matakuliah,
                    'nama_matakuliah' => $nama_matakuliah,
                    'jenis_matakuliah' => $jenis_mata_kuliah,
                    'kode_jurusan' => $kode_jurusan,
                    'bobot_tatap_muka' => $bobot_tatap_muka,
                    'bobot_pratikum' => $bobot_praktikum,
                    'bobot_praktek_lapangan' => $bobot_praktek_lapangan,
                    'bobot_simulasi' => $bobot_simulasi,
                    'bobot_matakuliah' => $bobot_mata_kuliah,
                    'metode_pembelajaran' => $metode_pembelajaran,
                    'tgl_mulai_efektif' => $tgl_mulai_efektif,
                    'tgl_akhir_efektif' => $tgl_akhir_efektif,
                    'id_semester' => $id_semester,
                    'status' => $status,
                    'crdt' => date('Y-m-d'),
                    'id_kampus' => $id_kampus,
                );

                DB::table('m_matakuliah')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function edit_matakuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $kode_matakuliah = $request->kode_mata_kuliah;
                $nama_matakuliah = $request->nama_mata_kuliah;
                $jenis_mata_kuliah = $request->jenis_mata_kuliah;
                $kode_jurusan = $request->kode_jurusan;
                $bobot_tatap_muka = $request->bobot_tatap_muka;
                $bobot_praktikum = $request->bobot_praktikum;
                $bobot_praktek_lapangan = $request->bobot_praktek_lapangan;
                $bobot_simulasi = $request->bobot_simulasi;
                $metode_pembelajaran = $request->metode_pembelajaran;
                $tgl_mulai_efektif = $request->tgl_mulai_efektif;
                $tgl_akhir_efektif = $request->tgl_akhir_efektif;
                $status = $request->status;
                $bobot_mata_kuliah = $request->bobot_mata_kuliah;
                $id_semester = $request->id_semester;

                $data_simpan = array(
                    'kode_matakuliah' => $kode_matakuliah,
                    'nama_matakuliah' => $nama_matakuliah,
                    'jenis_matakuliah' => $jenis_mata_kuliah,
                    'kode_jurusan' => $kode_jurusan,
                    'bobot_tatap_muka' => $bobot_tatap_muka,
                    'bobot_pratikum' => $bobot_praktikum,
                    'bobot_praktek_lapangan' => $bobot_praktek_lapangan,
                    'bobot_simulasi' => $bobot_simulasi,
                    'bobot_matakuliah' => $bobot_mata_kuliah,
                    'metode_pembelajaran' => $metode_pembelajaran,
                    'tgl_mulai_efektif' => $tgl_mulai_efektif,
                    'tgl_akhir_efektif' => $tgl_akhir_efektif,
                    'id_semester' => $id_semester,
                    'status' => $status,
                );


                DB::table('m_matakuliah')->where('id_matakuliah', $request->id_mata_kuliah)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_matakuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $id_matakuliah = $request->id_mata_kuliah;
                DB::table('m_matakuliah')->where('id_matakuliah', $id_matakuliah)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function list_ruangan(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');

            $kode_ruangans = $req->get('kode_ruangan');
            $statuss = $req->get('status');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }


            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }

            if ($kode_ruangans) {
                $kode_ruangan = $kode_ruangans;
            } else {
                $kode_ruangan = '';
            }



            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }

            $ruangans = DB::table('m_ruangan')
                ->where('id_kampus', $id_kampus);


            if ($search) {
                $ruangan = $ruangans->where('nama_ruangan', 'LIKE', '%' . $search . '%')->paginate($limit, ['*'], 'page', $page);
            } else {
                $ruangan = $ruangans->paginate($limit, ['*'], 'page', $page);
            }

            if ($kode_ruangan) {
                $ruangan = $ruangans->where('kode_ruangan', $kode_ruangan)->paginate($limit, ['*'], 'page', $page);
            } else {
                $ruangan = $ruangans->paginate($limit, ['*'], 'page', $page);
            }

            if ($status == '1' || $status == '0') {
                if ($status == '1') {
                    $ruangan = $ruangans->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == '0') {
                    $ruangan = $ruangans->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $ruangan = $ruangans->paginate($limit, ['*'], 'page', $page);
            }

            $dari = $ruangan->currentPage();
            $hingga = $ruangan->lastPage();
            $totaldata = $ruangan->total();
            $totalhalaman = $ruangan->count();
            $data = $ruangan->items();

            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function tambah_ruangan(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'kode_ruangan' => $request->kode_ruangan,
                    'nama_ruangan' => $request->nama_ruangan,
                    'id_kampus' => $id_kampus,
                    'status' => $request->status,
                    'crdt' => date('Y-m-d'),
                );

                DB::table('m_ruangan')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function edit_ruangan(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $data_simpan = array(
                    'kode_ruangan' => $request->kode_ruangan,
                    'nama_ruangan' => $request->nama_ruangan,
                    'id_kampus' => $payload->id_kampus,
                    'status' => $request->status,
                );

                DB::table('m_ruangan')->where('id_ruangan', $request->id_ruangan)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function hapus_ruangan(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                DB::table('m_ruangan')->where('id_ruangan', $request->id_ruangan)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_grade(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $limits = $req->get('limit');


                $grade = $req->get('grade');
                $statuss = $req->get('status');
                $predikats = $req->get('predikat');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }

                if (isset($statuss)) {
                    $status = $statuss == '1' ? '1' : '0';
                } else {
                    $status = 'null';
                }

                if ($predikats) {
                    $predikat = $predikats;
                } else {
                    $predikat = '';
                }

                $grades = DB::table('grade')
                    ->where('id_kampus', $id_kampus);


                if ($grade) {
                    $grade = $grades->where('grade', $grade)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $grade = $grades->paginate($limit, ['*'], 'page', $page);
                }

                if ($predikat) {
                    $grade = $grades->where('predikat', $predikat)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $grade = $grades->paginate($limit, ['*'], 'page', $page);
                }

                if ($status == '1' || $status == '0') {
                    if ($status == '1') {
                        $grade = $grades->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                    } elseif ($status == '0') {
                        $grade = $grades->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                    }
                } else {
                    $grade = $grades->paginate($limit, ['*'], 'page', $page);
                }

                $dari = $grade->currentPage();
                $hingga = $grade->lastPage();
                $totaldata = $grade->total();
                $totalhalaman = $grade->count();
                $data = $grade->items();

                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function tambah_grade(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'grade' => $request->grade,
                    'bobot' => $request->bobot,
                    'range_awal' => $request->range_awal,
                    'range_akhir' => $request->range_akhir,
                    'predikat' => $request->predikat,
                    'status' => $request->status,
                    'crdt' => date('Y-m-d'),
                    'id_kampus' => $id_kampus
                );

                DB::table('grade')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function edit_grade(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                $data_simpan = array(
                    'grade' => $request->grade,
                    'bobot' => $request->bobot,
                    'range_awal' => $request->range_awal,
                    'range_akhir' => $request->range_akhir,
                    'predikat' => $request->predikat,
                    'status' => $request->status,
                    'id_kampus' => $payload->id_kampus
                );

                DB::table('grade')->where('id_grade', $request->id_grade)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function hapus_grade(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                DB::table('grade')->where('id_grade', $request->id_grade)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_jadwal_kuliah(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $limits = $req->get('limit');


            $id_matkuls = $req->get('id_mata_kuliah');
            $id_semesters = $req->get('id_semester');
            $searchs = $req->get('search');
            $statuss = $req->get('status');
            $harii = $req->get('hari');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }


            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }

            if ($id_matkuls) {
                $id_matkul = $id_matkuls;
            } else {
                $id_matkul = '';
            }

            if ($id_semesters) {
                $id_semester = $id_semesters;
            } else {
                $id_semester = '';
            }

            if ($harii) {
                $hari = $harii;
            } else {
                $hari = '';
            }


            $jadwal_kuliahs = DB::table('m_jadwal_kuliah')->where('id_kampus', $id_kampus);

            if ($id_matkul) {
                $jadwal_kuliah = $jadwal_kuliahs->where('id_mata_kuliah', $id_matkul)->paginate($limit, ['*'], 'page', $page);
            } else {
                $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
            }

            if ($hari) {
                $jadwal_kuliah = $jadwal_kuliahs->where('hari', $hari)->paginate($limit, ['*'], 'page', $page);
            } else {
                $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
            }

            if ($status == '1' || $status == '0') {
                if ($status == '1') {
                    $jadwal_kuliah = $jadwal_kuliahs->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == '0') {
                    $jadwal_kuliah = $jadwal_kuliahs->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
            }

            if ($id_semester) {
                $jadwal_kuliah = $jadwal_kuliahs->where('id_semester', $id_semester)->paginate($limit, ['*'], 'page', $page);
            } else {
                $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
            }
            foreach ($jadwal_kuliah as $v) {
                $data2[] = array(

                    'id_jadwal_kuliah' => $v->id_jadwal_kuliah,
                    'id_mata_kuliah' => $v->id_mata_kuliah,
                    'nama_mata_kuliah' => $this->nama_mata_kuliah($v->id_mata_kuliah),
                    'id_dosen' => $v->id_dosen,
                    'hari' => $v->hari,
                    'jam_mulai' => $v->jam_mulai,
                    'jam_selesai' => $v->jam_selesai,
                    'id_semester' => $v->id_semester,
                    'nama_semester' => $this->nama_semester($v->id_semester),
                    'sks' => $v->sks,
                    'id_kelas' => $v->id_kelas,
                    'nama_kelas' => $this->nama_kelas_abc($v->id_kelas),
                    'id_ruangan' => $v->id_ruangan,
                    'nama_ruangan' => $this->nama_ruangan($v->id_ruangan),
                    'status' => $v->status,
                    'crdt' => $v->crdt,
                    'id_kampus' => $v->id_kampus

                );
            }


            $dari = $jadwal_kuliah->currentPage();
            $hingga = $jadwal_kuliah->lastPage();
            $totaldata = $jadwal_kuliah->total();
            $totalhalaman = $jadwal_kuliah->count();

            $data = $data2;

            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function tambah_jadwal_kuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'id_mata_kuliah' => $request->id_mata_kuliah,
                    'id_dosen' => $request->id_dosen,
                    'hari' => $request->hari,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'id_semester' => $request->id_semester,
                    'sks' => $request->sks,
                    'id_kelas' => $request->id_kelas,
                    'id_ruangan' => $request->id_ruangan,
                    'status' => $request->status,
                    'crdt' => date('Y-m-d'),
                    'id_kampus' => $id_kampus
                );

                DB::table('m_jadwal_kuliah')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function edit_jadwal_kuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'id_mata_kuliah' => $request->id_mata_kuliah,
                    'id_dosen' => $request->id_dosen,
                    'hari' => $request->hari,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'id_semester' => $request->id_semester,
                    'sks' => $request->sks,
                    'id_kelas' => $request->id_kelas,
                    'id_ruangan' => $request->id_ruangan,
                    'status' => $request->status,
                );

                DB::table('m_jadwal_kuliah')->where('id_jadwal_kuliah', $request->id_jadwal_kuliah)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function hapus_jadwal_kuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                DB::table('m_jadwal_kuliah')->where('id_jadwal_kuliah', $request->id_jadwal_kuliah)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_peserta_kuliah(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $limits = $req->get('limit');


                $id_matkuls = $req->get('id_mata_kuliah');
                $id_semesters = $req->get('id_semester');
                $searchs = $req->get('search');
                $statuss = $req->get('status');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }

                if (isset($statuss)) {
                    $status = $statuss == '1' ? '1' : '0';
                } else {
                    $status = 'null';
                }

                if ($id_matkuls) {
                    $id_matkul = $id_matkuls;
                } else {
                    $id_matkul = '';
                }

                if ($id_semesters) {
                    $id_semester = $id_semesters;
                } else {
                    $id_semester = '';
                }


                $jadwal_kuliahs = DB::table('m_peserta_kuliah')->where('id_kampus', $id_kampus);

                if ($id_matkul) {
                    $jadwal_kuliah = $jadwal_kuliahs->where('id_mata_kuliah', $id_matkul)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
                }
                if ($status == '1' || $status == '0') {
                    if ($status == '1') {
                        $jadwal_kuliah = $jadwal_kuliahs->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                    } elseif ($status == '0') {
                        $jadwal_kuliah = $jadwal_kuliahs->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                    }
                } else {
                    $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
                }

                if ($id_semester) {
                    $jadwal_kuliah = $jadwal_kuliahs->where('id_semester', $id_semester)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $jadwal_kuliah = $jadwal_kuliahs->paginate($limit, ['*'], 'page', $page);
                }
                $dari = $jadwal_kuliah->currentPage();
                $hingga = $jadwal_kuliah->lastPage();
                $totaldata = $jadwal_kuliah->total();
                $totalhalaman = $jadwal_kuliah->count();
                $data = $jadwal_kuliah->items();

                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function tambah_peserta_kuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'hari' => $request->hari,
                    'jam' => $request->jam,
                    'id_mata_kuliah' => $request->id_mata_kuliah,
                    'id_semester' => $request->id_semester,
                    'sks' => $request->sks,
                    'id_kelas' => $request->id_kelas,
                    'id_dosen' => $request->id_dosen,
                    'id_ruangan' => $request->id_ruangan,
                    'id_peserta' => $request->id_peserta,
                    'status' => $request->status,
                    'crdt' => date('Y-m-d'),
                    'id_kampus' => $id_kampus
                );

                DB::table('m_peserta_kuliah')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function edit_peserta_kuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'hari' => $request->hari,
                    'jam' => $request->jam,
                    'id_mata_kuliah' => $request->id_mata_kuliah,
                    'id_semester' => $request->id_semester,
                    'sks' => $request->sks,
                    'id_kelas' => $request->id_kelas,
                    'id_dosen' => $request->id_dosen,
                    'id_ruangan' => $request->id_ruangan,
                    'id_peserta' => $request->id_peserta,
                    'status' => $request->status,
                    'id_kampus' => $id_kampus
                );

                DB::table('m_peserta_kuliah')->where('id_peserta_kuliah', $request->id_peserta_kuliah)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function hapus_peserta_kuliah(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                DB::table('m_peserta_kuliah')->where('id_peserta_kuliah', $request->id_peserta_kuliah)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_peminatan_dosen(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');
                $limits = $req->get('limit');


                $kodes = $req->get('kode');
                $id_fakultass = $req->get('id_fakultas');
                $search = $req->get('search');
                $statuss = $req->get('status');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }

                if (isset($statuss)) {
                    $status = $statuss == '1' ? '1' : '0';
                } else {
                    $status = 'null';
                }

                if ($kodes) {
                    $kode = $kodes;
                } else {
                    $kode = '';
                }

                if ($id_fakultass) {
                    $id_fakultas = $id_fakultass;
                } else {
                    $id_fakultas = '';
                }

                $peminatan_dosens = DB::table('peminatan_dosen')->where('id_kampus', $id_kampus);

                if ($search) {
                    $peminatan_dosen = $peminatan_dosens->where('dekan', 'LIKE', '%' . $search . '%')->paginate($limit, ['*'], 'page', $page);
                } else {
                    $peminatan_dosen = $peminatan_dosens->paginate($limit, ['*'], 'page', $page);
                }

                if ($kode) {
                    $peminatan_dosen = $peminatan_dosens->where('kode', $kode)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $peminatan_dosen = $peminatan_dosens->paginate($limit, ['*'], 'page', $page);
                }

                if ($status == '1' || $status == '0') {
                    if ($status == '1') {
                        $peminatan_dosen = $peminatan_dosens->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                    } elseif ($status == '0') {
                        $peminatan_dosen = $peminatan_dosens->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                    }
                } else {
                    $peminatan_dosen = $peminatan_dosens->paginate($limit, ['*'], 'page', $page);
                }

                if ($id_fakultas) {
                    $peminatan_dosen = $peminatan_dosens->where('id_fakultas', $id_fakultas)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $peminatan_dosen = $peminatan_dosens->paginate($limit, ['*'], 'page', $page);
                }
                $dari = $peminatan_dosen->currentPage();
                $hingga = $peminatan_dosen->lastPage();
                $totaldata = $peminatan_dosen->total();
                $totalhalaman = $peminatan_dosen->count();
                $data = $peminatan_dosen->items();

                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function tambah_peminatan_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'kode' => $request->kode,
                    'id_fakultas' => $request->id_fakultas,
                    'dekan' => $request->dekan,
                    'status' => $request->status,
                    'crdt' => date('Y-m-d'),
                    'id_kampus' => $id_kampus
                );

                DB::table('peminatan_dosen')->insert($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function edit_peminatan_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $data_simpan = array(
                    'kode' => $request->kode,
                    'id_fakultas' => $request->id_fakultas,
                    'dekan' => $request->dekan,
                    'status' => $request->status,
                    'id_kampus' => $id_kampus
                );

                DB::table('peminatan_dosen')->where('id_peminatan_dosen', $request->id_peminatan_dosen)->update($data_simpan);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function hapus_peminatan_dosen(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {

                DB::table('peminatan_dosen')->where('id_peminatan_dosen', $request->id_peminatan_dosen)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_kurikulum(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $limits = $req->get('limit');

            $search = $req->get('search');
            $statuss = $req->get('status');
            $kode_jurusans = $req->get('kode_jurusan');
            $id_tahun_ajars = $req->get('id_tahun_ajar');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }

            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }

            if ($id_tahun_ajars) {
                $id_tahun_ajar = $id_tahun_ajars;
            } else {
                $id_tahun_ajar = '';
            }

            $kurikulums = DB::table('kurikulum')->where('id_kampus', $id_kampus);

            if ($search) {
                $kurikulum = $kurikulums->where('nama_kurikulum', 'LIKE', '%' . $search . '%')->paginate($limit, ['*'], 'page', $page);
            } else {
                $kurikulum = $kurikulums->paginate($limit, ['*'], 'page', $page);
            }
            if ($kode_jurusan) {
                $kurikulum = $kurikulums->where('kode_jurusan', $kode_jurusan)->paginate($limit, ['*'], 'page', $page);
            } else {
                $kurikulum = $kurikulums->paginate($limit, ['*'], 'page', $page);
            }

            if ($id_tahun_ajar) {
                $kurikulum = $kurikulums->where('id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*'], 'page', $page);
            } else {
                $kurikulum = $kurikulums->paginate($limit, ['*'], 'page', $page);
            }

            if ($status == '1' || $status == '0') {
                if ($status == '1') {
                    $kurikulum = $kurikulums->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == '0') {
                    $kurikulum = $kurikulums->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $kurikulum = $kurikulums->paginate($limit, ['*'], 'page', $page);
            }

            foreach ($kurikulum as $value) {
                $data2[] = array(
                    'id_kurikulum' => $value->id_kurikulum,
                    'nama_kurikulum' => $value->nama_kurikulum,
                    'kode_jurusan' => $value->kode_jurusan,
                    'nama_jurusan' => $this->nama_prodi($value->kode_jurusan),
                    'id_tahun_ajar' => $value->id_tahun_ajar,
                    'aturan_sks_lulus' => $value->aturan_sks_lulus,
                    'aturan_sks_wajib' => $value->aturan_sks_wajib,
                    'aturan_sks_pilihan' => $value->aturan_sks_pilihan,
                    'aturan_sks_matkul_wajib' => $value->aturan_sks_matkul_wajib,
                    'aturan_sks_matkul_pilihan' => $value->aturan_sks_matkul_pilihan,
                    'jumlah_sks_pilihan_wajib' => $value->jumlah_sks_pilihan_wajib,
                    'crdt' => $value->crdt,
                    'status' => $value->status,
                    'id_kampus' => $value->id_kampus
                );
            }

            $dari = $kurikulum->currentPage();
            $hingga = $kurikulum->lastPage();
            $totaldata = $kurikulum->total();
            $totalhalaman = $kurikulum->count();
            $data = $data2;

            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function tambah_kurikulum(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;
                // $request->aturan_matkul_wajib + $request->aturan_matkul_pilihan
                $tambah = array(
                    'nama_kurikulum' => $request->nama_kurikulum,
                    'kode_jurusan' => $request->kode_jurusan,
                    'id_tahun_ajar' => $request->id_tahun_ajar,
                    'aturan_sks_matkul_wajib' => $request->aturan_matkul_wajib,
                    'aturan_sks_matkul_pilihan' => $request->aturan_matkul_pilihan,
                    'jumlah_sks_pilihan_wajib' => $request->jumlah_sks_pilihan_wajib,
                    'crdt' => date('Y-m-d'),
                    'status' => $request->status,
                    'id_kampus' => $id_kampus
                );

                DB::table('kurikulum')->insert($tambah);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function ubah_kurikulum(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;

                $update = array(
                    'nama_kurikulum' => $request->nama_kurikulum,
                    'kode_jurusan' => $request->kode_jurusan,
                    'id_tahun_ajar' => $request->id_tahun_ajar,
                    'aturan_sks_matkul_wajib' => $request->aturan_matkul_wajib,
                    'aturan_sks_matkul_pilihan' => $request->aturan_matkul_pilihan,
                    'status' => $request->status,
                );

                DB::table('kurikulum')->where('id_kurikulum', $request->id_kurikulum)->update($update);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function hapus_kurikulum(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_kampus = $payload->id_kampus;
                DB::table('kurikulum')->where('id_kurikulum', $request->id_kurikulum)->delete();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function jenis_mata_kuliah(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');



            $id_tahun_ajar = $req->get('id_tahun_ajar');
            $statuss = $req->get('status');
            $kode_jurusans = $req->get('kode_jurusan');



            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }


            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            if ($kode_jurusans) {
                $kode_jurusan = $kode_jurusans;
            } else {
                $kode_jurusan = '';
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }

            $jenis_matkuls = \DB::table('kelompok_matakuliah')->select('id_kelompok_matakuliah', 'kode', 'nama', 'status')->paginate($limit, ['*'], 'page', $page);

            $dari = $jenis_matkuls->currentPage();
            $hingga = $jenis_matkuls->lastPage();
            $totaldata = $jenis_matkuls->total();
            $totalhalaman = $jenis_matkuls->count();
            $data = $jenis_matkuls->items();


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
    function list_kelas(Request $req)
    {
        //error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $pages = $req->get('pages');
            $search = $req->get('search');
            $limits = $req->get('limit');

            $kode_ruangans = $req->get('kode_ruangan');
            $statuss = $req->get('status');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }


            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }

            if ($kode_ruangans) {
                $kode_ruangan = $kode_ruangans;
            } else {
                $kode_ruangan = '';
            }



            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }

            $kelass = DB::table('m_kelas')
                ->where('id_kampus', $id_kampus);


            if ($search) {
                $kelas = $kelass->where('nama_kelas', 'LIKE', '%' . $search . '%')->paginate($limit, ['*'], 'page', $page);
            } else {
                $kelas = $kelass->paginate($limit, ['*'], 'page', $page);
            }

            if ($kode_ruangan) {
                $kelas = $kelass->where('kode_kelas', $kode_ruangan)->paginate($limit, ['*'], 'page', $page);
            } else {
                $kelas = $kelass->paginate($limit, ['*'], 'page', $page);
            }

            if ($status == '1' || $status == '0') {
                if ($status == '1') {
                    $kelas = $kelass->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == '0') {
                    $kelas = $kelass->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $kelas = $kelass->paginate($limit, ['*'], 'page', $page);
            }

            $dari = $kelas->currentPage();
            $hingga = $kelas->lastPage();
            $totaldata = $kelas->total();
            $totalhalaman = $kelas->count();
            $data = $kelas->items();

            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function update_akte(Request $req)
    {


        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $data_update = array(
                'no_sk_pendirian' => $req->no_sk_pendirian,
                'tanggal_sk_pendirian' => $req->tanggal_sk_pendirian,
                'status_kepemilikan' => $req->status_kepemilikan,
                'status_perguruan_tinggi' => $req->status_perguruan_tinggi,
                'sk_izin_operasional' => $req->sk_izin_operasional,
                'tanggal_izin_operasi' => $req->tanggal_izin_operasi,

                // 'bank'=>$req->bank,
                // 'unit_cabang'=>$req->unit_cabang,
                // 'no_rekening'=>$req->no_rekening,
                // 'mbs'=>$req->mbs,

                //  'luas_tanah_milik'=>$req->luas_tanah_milik,
                //   'luas_tanah_bukan_milik'=>$req->luas_tanah_bukan_milik,

            );

            DB::table('kampus')->where('id', $id_kampus)->update($data_update);

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function update_informasi_pt(Request $req)
    {
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;

            $data_update = array(
                // 'no_sk_pendirian'=>$req->no_sk_pendirian,
                // 'tanggal_sk_pendirian'=>$req->tanggal_sk_pendirian,
                // 'status_kepemilikan'=>$req->status_kepemilikan,
                // 'status_perguruan_tinggi'=>$req->status_perguruan_tinggi,
                // 'sk_izin_operasional'=>$req->sk_izin_operasional,
                // 'tanggal_izin_operasi'=>$req->tanggal_izin_operasi,

                'bank' => $req->bank,
                'unit_cabang' => $req->unit_cabang,
                'no_rekening' => $req->no_rekening,
                'mbs' => $req->mbs,

                'luas_tanah_milik' => $req->luas_tanah_milik,
                'luas_tanah_bukan_milik' => $req->luas_tanah_bukan_milik,

            );

            DB::table('kampus')->where('id', $id_kampus)->update($data_update);

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
}
