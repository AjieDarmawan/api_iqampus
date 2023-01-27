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
use App\Models\mahasiswa;
use App\Models\MasterKampus;
use App\Models\MasterKomponen;
use App\Models\Role;
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

class MahasiswaController extends Controller
{
    protected $jwt;
    use utility;


    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function index(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            // DB::transaction(function () use ($payload, $req) {

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;



            $pages = $req->get('pages');

            $search = $req->get('search');

            $limits = $req->get('limit');

            $daris = $req->get('dari');
            $hinggas = $req->get('hingga');

            $pmb_statuss = $req->get('pmb_status');

            $data_statuss = $req->get('data_status');

            $id_tahun_ajars = $req->get('id_tahun_ajar');



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

            if ($id_tahun_ajars) {
                $id_tahun_ajar = $id_tahun_ajars;
            } else {
                $id_tahun_ajar = '';
            }





            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            $mhss = \DB::table('mahasiswa')
                ->join('auth', 'mahasiswa.id_auth', '=', 'auth.id')
                ->join('pmb_tahapan_pendaftaran as pt', 'pt.id_auth', '=', 'mahasiswa.id_auth')
                ->where('pt.total', '>=', $data_status)
                ->where('auth.id_kampus', $id_kampus)
                ->where('auth.akses', 'pmb')
                ->where('mahasiswa.status', '!=', 99)
                ->whereIn('mahasiswa.pmb_status', $pmb_status);

            if ($daris) {
                $mhs = $mhss
                    ->where('nama', 'LIKE', '%' . $search . '%')
                    ->whereBetween(DB::raw('DATE(mahasiswa.created_at)'), [$daris, $hinggas])
                    ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
            } else {
                $mhs = $mhss
                    ->where('nama', 'LIKE', '%' . $search . '%')
                    ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
            }


            if ($id_tahun_ajar) {

                if ($daris) {
                    $mhs = $mhss
                        ->where('mahasiswa.id_tahun_ajar', $id_tahun_ajar)
                        ->orderBy('mahasiswa.id_tahun_ajar', 'DESC')
                        ->whereBetween(DB::raw('DATE(mahasiswa.created_at)'), [$daris, $hinggas])
                        ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
                } else {
                    $mhs = $mhss
                        ->where('mahasiswa.id_tahun_ajar', $id_tahun_ajar)
                        ->orderBy('mahasiswa.id_tahun_ajar', 'DESC')
                        ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
                }
            } else {
                if ($daris) {
                    $mhs = $mhss->whereBetween(DB::raw('DATE(mahasiswa.created_at)'), [$daris, $hinggas])
                        ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
                } else {
                    $mhs = $mhss->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
                }
            }


            // $mhs = \DB::table('mahasiswa')
            // ->join('auth', 'mahasiswa.id_auth', '=', 'auth.id')
            // ->join('pmb_tahapan_pendaftaran as pt', 'pt.id_auth', '=', 'mahasiswa.id_auth')
            // ->where('pt.total', '>=', $data_status)
            // ->where('auth.id_kampus', $id_kampus)
            // ->where('mahasiswa.status', '!=', 99)
            // ->where('auth.akses', 'pmb')
            // ->whereIn('mahasiswa.pmb_status', $pmb_status)
            // ->whereBetween(DB::raw('DATE(mahasiswa.created_at)'), [$daris, $hinggas])
            // ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);





            $dari = $mhs->currentPage();
            $hingga = $mhs->lastPage();
            $totaldata = $mhs->total();
            $totalhalaman = $mhs->count();
            $data = $mhs->items();

            error_reporting(0);

            foreach ($data as $d) {

                $kouta = DB::table('m_program_studi as p')
                    ->select('kouta')
                    ->where('kode', $d->jurusan1)
                    ->where('id_kelas', $d->id_kelas)
                    ->where('id_kelompok_jenjang', $d->id_kelompok_jenjang)
                    ->first();

                // echo "<pre>";
                // print_r($kouta);
                // die;

                $kouta_mhs = DB::table('mahasiswa as p')
                    ->select('id', 'nama')
                    ->where('jurusan1', $d->jurusan1)
                    ->where('id_kelas', $d->id_kelas)
                    ->where('id_kelompok_jenjang', $d->id_kelompok_jenjang)
                    ->where('pmb_status', 'lulus')
                    ->sum('id');





                $jadwal_ujian_query = DB::select("select jadwal_ujian,id_pmb_jadwal from pmb_jadwal where id_gelombang = '" . $d->id_gelombang . "' and tgl_rate_gelombang_mulai <= '" . date($d->created_at) . "' and tgl_rate_gelombang_selesai >= '" . date($d->created_at) . "'");

                error_reporting(0);
                if ($d->pmb_jadwal_ujian) {
                    $jadwal_ujian = $d->pmb_jadwal_ujian;
                } else {
                    $jadwal_ujian = $jadwal_ujian_query[0]->jadwal_ujian;
                }


                $data2[] = array(
                    'id_mahasiswa'            => $d->id_mahasiswa,
                    'id_auth'       => $d->id_auth,
                    'nama'          => $d->nama,
                    'jenis_kelamin' => $d->jenis_kelamin,
                    'tanggal_lahir' => $d->tanggal_lahir,
                    'tempat_lahir'  => $d->tempat_lahir,
                    'nama_ibu'      => $d->nama_ibu,
                    'agama'         => $d->agama,
                    'kewarganegaraan' => $d->kewarganegaraan,
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
                    'kelurahan' =>  $d->kelurahan,
                    'kode_pos' =>  $d->kode_pos,
                    'penerima_kps' =>  $d->penerima_kps,
                    'kecamatan' =>  $d->kecamatan,
                    'jenis_tinggal' =>  $d->jenis_tinggal,
                    'id_alat_transport' =>  $d->id_alat_transport,
                    'nik_ayah' =>  $d->nik_ayah,
                    'nama_ayah' =>  $d->nama_ayah,
                    'tanggal_lahir_ayah' =>  $d->tanggal_lahir_ayah,
                    'id_jenj_didik_ayah' =>  $d->id_jenj_didik_ayah,
                    'id_pekerjaan_ayah' =>  $d->id_pekerjaan_ayah,
                    'id_penghasilan_ayah' =>  $d->id_penghasilan_ayah,
                    'nik_ibu' =>  $d->nik_ibu,
                    'tanggal_lahir_ibu' =>  $d->tanggal_lahir_ibu,
                    'id_jenj_didik_ibu' =>  $d->id_jenj_didik_ibu,
                    'id_pekerjaan_ibu' => $d->id_pekerjaan_ibu,
                    'id_penghasilan_ibu' => $d->id_penghasilan_ibu,
                    'nama_wali' => $d->nama_wali,
                    'id_jenj_didik_wali' => $d->id_jenj_didik_wali,
                    'id_pekerjaan_wali' => $d->id_pekerjaan_wali,
                    'id_penghasilan_wali' => $d->id_penghasilan_wali,
                    'created_at' => $d->created_at,
                    'updated_at' => $d->updated_at,
                    'pmb_no_pendaftaran' => $d->pmb_no_pendaftaran,
                    'asal_sekolah' => $d->asal_sekolah,
                    'jurusan1' => $d->jurusan1,
                    'jurusan2' => $d->jurusan2,
                    'jurusan3' => $d->jurusan3,
                    'tanggal_lahir_wali' => $d->pmb_no_pendaftaran,
                    'id_gelombang' => $d->id_gelombang,
                    'email' => $d->email,
                    // 'password' => $d->password,
                    'id_kampus' => $d->id_kampus,
                    'nama_lengkap' => $d->nama_lengkap,
                    'foto' =>  $d->foto,
                    'status' => $d->status,
                    'pmb_status' => $d->pmb_status,
                    // 'email_verified_at' =>  $d->email_verified_at,
                    // 'remember_token' => $d->remember_token,
                    'akses' => $d->akses,
                    // 'tanggal_masuk' => $d->tanggal_masuk,
                    // 'tanggal_keluar' => $d->tanggal_keluar,
                    'tahapan_proses' => $d->tahapan . '/' . '5',
                    'jadwal_ujian' => $jadwal_ujian,
                    'kouta_jurusan' => $kouta->kouta,
                    'kouta_terpenuhi' => $kouta_mhs,


                );
            }

            if ($req->tipe == 'pmb') {
                return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data2));
            } else {
                return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data2));
            }
            // });
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

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nama_lengkap' => 'required',
            'email' => 'required|email',
            'nomor_wa' => 'required',
            'asal_sekolah' => 'required|email',
            'jurusan_diminati1' => 'required',
            // 'id_kampus' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json([
                "status" => 500,
                "message" => "gagal",
                "error" => $validated->errors(),
                "data" => ""
            ]);
        }
        $pass = Str::random(10);

        try {
            $id_kampus = array_shift((explode('.', $_SERVER['HTTP_HOST'])));
            $mahasiswa = \DB::table('mahasiswa')->insertGetId([
                "email" => $request->email,
                "password" => Hash::make($pass),
                "decode_password" => $pass,
                "nama_lengkap" => $request->nama_lengkap,
                "nomor_wa" => $request->nomor_wa,
                "asal_sekolah" => $request->asal_sekolah,
                "jurusan_diminati1" => $request->jurusan_diminati1,
                "jurusan_diminati2" => $request->has('jurusan_diminati2') ? $request->jurusan_diminati2 : null,
                "id_kampus" => $id_kampus
            ]);
            Log::info('mahasiswa success to save.', ['id' => $mahasiswa]);
            $emailJob = (new SubmitEmailJob($request->email, ["nama" => $request->nama, "email" => $request->email, "password" => $pass]));
            dispatch($emailJob);

            return response()->json([
                "status" => 200,
                "message" => "berhasil",
                "error" => null,
                "data" => $pass
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "gagal",
                "error" => $e->getMessage(),
                "data" => ""
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'nama_lengkap'          => 'required',
            'jenis_kelamin'         => 'required',
            'tanggal_lahir'         => 'required',
            'tempat_lahir'          => 'required',
            'agama'                 => 'required',
            'nomor_wa'              => 'required',
            'alamat'                => 'required',
            'asal_sekolah'          => 'required',
            'status_tes'            => 'required',
            'tanggal_lulus_tes'     => 'required',
            'status_mahasiswa'      => 'required',
            'jurusan_diminati1'     => 'required'
        ]);

        if ($validated->fails()) {
            return response()->json(["status" => 500, "message" => "gagal", "error" => $validated->errors()]);
        }

        $data = [
            'nama_lengkap'          => $request->nama_lengkap,
            'jenis_kelamin'         => $request->jenis_kelamin,
            'tanggal_lahir'         => $request->tanggal_lahir,
            'tempat_lahir'          => $request->tempat_lahir,
            'agama'                 => $request->agama,
            'nomor_wa'              => $request->nomor_wa,
            'alamat'                => $request->alamat,
            'asal_sekolah'          => $request->asal_sekolah,
            'status_tes'            => $request->status_tes,
            'tanggal_lulus_tes'     => $request->tanggal_lulus_tes,
            'status_mahasiswa'      => $request->status_mahasiswa,
            'jurusan_diminati1'     => $request->jurusan_diminati1
        ];
        if ($request->has('jurusan_diminati2')) {
            $data['jurusan_diminati2'] = $request->jurusan_diminati2;
        }

        try {
            $payload = collect(JWTAuth::parseToken()->getPayload()->get('data'));
            \DB::table('mahasiswa')->where('id', $payload->id)->update($data);
            Log::info('mahasiswa success to update.', ['id' => $payload->id]);

            return response()->json([
                "status" => 200,
                "message" => "berhasil",
                "error" => null,
                "data" => ""
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "gagal",
                "error" => $e->getMessage(),
                "data" => ""
            ]);
        }
    }


    function detail_mhs(Request $request)
    {
        try {

            error_reporting(0);
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                $mhs = DB::table('mahasiswa')
                    ->join('auth', 'auth.id', '=', 'mahasiswa.id_auth')
                    ->where('mahasiswa.id_auth', $id_auth)
                    ->where('auth.id_kampus', $id_kampus)
                    ->select('mahasiswa.*')
                    ->first();

                $berkas = DB::table('berkas_mahasiswa')
                    ->where('id_auth', $id_auth)
                    ->where('tipe_file', 'foto')
                    ->first();

                $auth = DB::table('auth')
                    ->where('id', $id_auth)
                    ->select('email')
                    ->first();

                if ($berkas->nama_file) {
                    $foto = asset('public/berkas_mahasiswa/foto/' . $berkas->nama_file);
                } else {
                    $foto = null;
                }

                $data_mhs = array(
                    "id" => $mhs->id,
                    "id_auth" => $mhs->id_auth,
                    "nama" => $mhs->nama,
                    'email' => $auth->email,
                    "jenis_kelamin" => $mhs->jenis_kelamin,
                    "tanggal_lahir" =>  $mhs->tanggal_lahir,
                    "tempat_lahir" =>  $mhs->tempat_lahir,
                    "nama_ibu" => $mhs->nama_ibu,
                    "agama" => $this->agama($mhs->agama),
                    "agama" => $mhs->agama,
                    "kewarganegaraan" => $mhs->kewarganegaraan,
                    "nik" => $mhs->nik,
                    "nisn" => $mhs->nisn,
                    "npwp" => $mhs->npwp,
                    "jalan" => $mhs->jalan,
                    "telp" => $mhs->telp,
                    "dusun" => $mhs->dusun,
                    "rt" => $mhs->rt,
                    "rw" => $mhs->rw,
                    "hp" => $mhs->hp,
                    "kelurahan" => $mhs->kelurahan,
                    "kode_pos" => $mhs->kode_pos,
                    "penerima_kps" => $mhs->penerima_kps,
                    "kecamatan" => $mhs->kecamatan,
                    "jenis_tinggal" => $mhs->jenis_tinggal,
                    "id_alat_transport" => $this->transportasi($mhs->id_alat_transport),
                    "id_alat_transport_key" => $mhs->id_alat_transport,
                    "nik_ayah" =>  $mhs->nik_ayah,
                    "nama_ayah" =>  $mhs->nama_ayah,
                    "tanggal_lahir_ayah" =>  $mhs->tanggal_lahir_ayah,
                    "id_jenj_didik_ayah" =>  $mhs->id_jenj_didik_ayah,
                    "id_pekerjaan_ayah" =>  $mhs->id_pekerjaan_ayah,
                    "id_penghasilan_ayah" =>  $mhs->id_penghasilan_ayah,
                    "nik_ibu" =>  $mhs->nik_ibu,
                    "tanggal_lahir_ibu" =>  $mhs->tanggal_lahir_ibu,
                    "id_jenj_didik_ibu" =>  $mhs->id_jenj_didik_ibu,
                    "id_pekerjaan_ibu" =>  $mhs->id_pekerjaan_ibu,
                    "id_penghasilan_ibu" =>  $mhs->id_penghasilan_ibu,
                    "nama_wali" =>  $mhs->nama_wali,
                    "id_jenj_didik_wali" =>  $mhs->id_jenj_didik_wali,
                    "id_pekerjaan_wali" =>  $mhs->id_pekerjaan_wali,
                    "id_penghasilan_wali" =>  $mhs->id_penghasilan_wali,
                    // "created_at"=> "2022-12-19 16=>26=>40",
                    // "updated_at"=> "2022-12-20 16=>48=>07",
                    "pmb_no_pendaftaran" => $mhs->pmb_no_pendaftaran,
                    "asal_sekolah" => $mhs->asal_sekolah,
                    "jurusan1" => $this->nama_prodi($mhs->jurusan1),
                    "jurusan2" => $this->nama_prodi($mhs->jurusan2),
                    "jurusan3" => $this->nama_prodi($mhs->jurusan3),

                    "jurusan1_key" => $mhs->jurusan1,
                    "jurusan2_key" => $mhs->jurusan2,
                    "jurusan3_key" => $mhs->jurusan3,


                    "tanggal_lahir_wali" => $mhs->tanggal_lahir_wali,
                    "id_gelombang" => $mhs->id_gelombang,
                    "status" => $mhs->status,
                    "tgl_pmb_batal" => $mhs->tgl_pmb_batal,
                    "pmb_status" => $mhs->pmb_status,
                    "pmb_status_date" =>  $mhs->pmb_status_date,
                    "pmb_jadwal_ujian" =>  $mhs->pmb_jadwal_ujian,
                    "jalur_masuk" => $mhs->jalur_masuk,
                    "kebutuhan_khusus" => $mhs->kebutuhan_khusus,
                    "provinsi" => $this->provinsi($mhs->provinsi),
                    "kota" =>  $this->provinsi($mhs->kota),

                    "provinsi_key" => $mhs->provinsi,
                    "kota_key" =>  $mhs->kota,
                    "id_kelompok_jenjang" =>  $mhs->id_kelompok_jenjang,
                    "id_kelas" =>  $mhs->id_kelas,


                    'foto' => $foto,
                );


                echo json_encode($this->show_data_object($data_mhs));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function mhs_hapus(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $id_mahasiswa      = $request->id_mahasiswa;

                $mhs_batal = array(
                    'tgl_pmb_batal' => date('Y-m-d H:i:s'),
                    'status' => 99,
                    'pmb_status' => 'hapus',
                );

                DB::table('mahasiswa')->where('id', $id_mahasiswa)->update($mhs_batal);

                echo json_encode($this->res_insert("sukses"));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function update_status_pmb(Request $request)
    {

        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $tipe      = $request->tipe;
                $id_mahasiswa_last      = $request->id_mahasiswa;
                $tgl_ujian         = $request->tgl_ujian;

                $id_mahasiswa = explode(',', $id_mahasiswa_last);


                // echo "<pre>";
                // print_r($id_mahasiswa);
                // die;
                //  $tipe      = $request->tipe;

                if ($tipe == 'jadwal_ujian') {

                    foreach ($id_mahasiswa as $key => $m) {
                        $update = array(
                            'pmb_jadwal_ujian' => $tgl_ujian,
                            //'status'=>99,
                        );

                        DB::table('mahasiswa')->where('id', $m)->update($update);
                    }
                }

                if ($tipe == 'lulus') {

                    foreach ($id_mahasiswa as $key => $m) {
                        $random = "12345678901234567890";

                        $update = array(
                            'pmb_status' => 'lulus',
                            'pmb_status_date' => date('Y-m-d H:i:s'),
                            'semester' => 1,
                            'status' => 1,
                            'nim' => substr(str_shuffle($random), 0, 20)
                        );

                        DB::table('mahasiswa')->where('id', $m)->update($update);

                        $id_auth = DB::table('mahasiswa')->where('id', $m)->select('id_auth')->first()->id_auth;

                        DB::table('auth')->where('id', $id_auth)->update(['akses' => '']);



                        DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $id_auth)->update(['info_kelulusan' => 1]);
                        $total = DB::select('select sum(formulir_pendaftaran+info_pembayaran+lengkapi_berkas+jadwal_uji_seleksi+info_kelulusan) as total from pmb_tahapan_pendaftaran  where id_auth = "' . $id_auth . '"');
                        DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $id_auth)->update(['total' => $total[0]->total]);
                    }
                }


                if ($tipe == 'tidak_lulus') {

                    foreach ($id_mahasiswa as $key => $m) {
                        $update = array(
                            'pmb_status' => 'tidak_lulus',
                            'pmb_status_date' => date('Y-m-d H:i:s'),
                            'status' => 0,
                        );

                        DB::table('mahasiswa')->where('id', $m)->update($update);
                    }
                }






                echo json_encode($this->res_insert("sukses"));
            });
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function mhs_tidak_lulus(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            error_reporting(0);


            $id_kampus = $payload->id_kampus;

            //  $id_auth        = $payload->id;
            // $id_kampus      = $payload->id_kampus;



            $pages = $request->get('pages');

            $search = $request->get('search');

            $limits = $request->get('limit');

            $daris = $request->get('dari');
            $hinggas = $request->get('hingga');

            $pmb_statuss = $request->get('pmb_status');

            $data_statuss = $request->get('data_status');

            $id_tahun_ajars = $request->get('id_tahun_ajar');



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

            if ($id_tahun_ajars) {
                $id_tahun_ajar = $id_tahun_ajars;
            } else {
                $id_tahun_ajar = '';
            }





            if ($pages) {
                $page = $pages;
            } else {
                $page = 1;
            }


            $mhs = \DB::table('mahasiswa')
                ->join('auth', 'mahasiswa.id_auth', '=', 'auth.id')
                ->join('pmb_tahapan_pendaftaran as pt', 'pt.id_auth', '=', 'mahasiswa.id_auth')
                ->where('pt.total', '>=', $data_status)
                ->where('auth.id_kampus', $id_kampus)
                ->where('nama', 'LIKE', '%' . $search . '%')
                ->where('auth.akses', 'pmb')
                ->whereIn('mahasiswa.status', [0, 2])
                ->whereIn('mahasiswa.pmb_status', ['tidak_lulus', 'pindah_jurusan']);


            if ($id_tahun_ajar) {
                $mhs = $mhs
                    ->where('mahasiswa.id_tahun_ajar', $id_tahun_ajar)
                    ->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
            } else {
                $mhs = $mhs->paginate($limit, ['*', 'mahasiswa.id as id_mahasiswa', 'pt.total as tahapan'], 'page', $page);
            }





            $dari = $mhs->currentPage();
            $hingga = $mhs->lastPage();
            $totaldata = $mhs->total();
            $totalhalaman = $mhs->count();
            $data = $mhs->items();





            foreach ($data as $d) {




                $pilihan_mhs = DB::table('mhs_perubahan_jrs as p')
                    ->select('kode_pilihan')
                    ->where('id_auth', $d->id_auth)
                    ->get();




                $data2[] = array(
                    'id_mahasiswa'            => $d->id_mahasiswa,
                    'id_auth'       => $d->id_auth,
                    'nama'          => $d->nama,
                    'id_kelas'      => $d->id_kelas,
                    'id_gelombang'  => $d->id_gelombang,
                    'id_kelompok_jenjang'  => $d->id_kelompok_jenjang,
                    'jurusan_diminati1' => $this->nama_prodi($d->jurusan1),
                    'kode_jurusan1' => $d->jurusan1,
                    'kouta_prodi1' => $this->kouta_prodi($d->jurusan1, $d->id_kelas, $d->id_kelompok_jenjang),
                    'kouta_terpenuhi1' => count($this->kouta_terpenuhi($d->jurusan1, $d->id_kelas, $d->id_kelompok_jenjang)),

                    'jurusan_diminati2' => $this->nama_prodi($pilihan_mhs[0]->kode_pilihan),
                    'kode_jurusan2' => $pilihan_mhs[0]->kode_pilihan,
                    'kouta_prodi2' => $this->kouta_prodi($pilihan_mhs[0]->kode_pilihan, $d->id_kelas, $d->id_kelompok_jenjang),
                    'kouta_terpenuhi2' => count($this->kouta_terpenuhi($pilihan_mhs[0]->kode_pilihan, $d->id_kelas, $d->id_kelompok_jenjang)),



                    'jurusan_diminati3' => $this->nama_prodi($pilihan_mhs[1]->kode_pilihan),
                    'kode_jurusan3' => $pilihan_mhs[1]->kode_pilihan,
                    'kouta_prodi3' => $this->kouta_prodi($pilihan_mhs[1]->kode_pilihan, $d->id_kelas, $d->id_kelompok_jenjang),
                    'kouta_terpenuhi3' => count($this->kouta_terpenuhi($pilihan_mhs[1]->kode_pilihan, $d->id_kelas, $d->id_kelompok_jenjang)),

                    // 'jurusan_diminati2'=>$this->nama_prodi($d->jurusan2),
                    // 'kode_jurusan2'=>$d->jurusan2,

                    // 'jurusan_diminati1'=>$this->nama_prodi($d->jurusan1),
                    // 'kode_jurusan1'=>$d->jurusan1,

                    // 'kouta_jurusan' => $kouta->kouta,
                    // 'kouta_terpenuhi' => $kouta_mhs,


                );
            }


            return response()->json($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data2));
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



    function mhs_prodi_pilihan(Request $request)
    {
        error_reporting(0);
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth = $payload->id;
            $id_kampus = $payload->id_kampus;


            $mhs = DB::table('mahasiswa as m')
                ->join('auth as a', 'a.id', '=', 'm.id_auth')
                ->where('a.id_kampus', $id_kampus)
                ->where('m.id_auth', $id_auth)
                ->select('m.jurusan1', 'm.jurusan2', 'm.jurusan3')
                ->first();


            $pilihan_mhs = DB::table('mhs_perubahan_jrs as p')
                ->select('kode_pilihan')
                ->where('id_auth', $d->id_auth)
                //->where('id_auth',147)
                ->get();



            foreach ($pilihan_mhs as $p) {

                if ($p->kode_pilihan == $mhs->jurusan2) {
                    $jurusan[] = $mhs->jurusan3;
                } elseif ($p->kode_pilihan == $mhs->jurusan3) {
                    $jurusan[] = $mhs->jurusan2;
                } else {
                    $jurusan[] = "dah_semua";
                }
            }


            // echo "<pre>";
            // print_r($jurusan);
            // echo  count($jurusan);
            //   die;


            //belum milih
            if ($pilihan_mhs[0]) {

                if (count($jurusan) >= 2) {
                    $data_json[] = array(
                        'jurusan2' => '',
                        'jurusan3' => '',
                        'kode_jurusan2' => '',
                        'kode_jurusan3' => '',
                    );
                } else {
                    $data_json[] = array(
                        'jurusan2' => $this->nama_prodi($jurusan[0]),
                        'jurusan3' => '',
                        'kode_jurusan2' => $jurusan[0],
                        'kode_jurusan3' => '',
                    );
                }
            } else {

                // tampilan awal
                $data_json[] = array(
                    'jurusan2' => $this->nama_prodi($mhs->jurusan2),
                    'jurusan3' => $this->nama_prodi($mhs->jurusan3),
                    'kode_jurusan2' => $mhs->jurusan2,
                    'kode_jurusan3' => $mhs->jurusan3,
                );
            }





            return response()->json($this->show_data_object($data_json));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function simpan_pilihan(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth = $payload->id;
            $id_kampus = $payload->id_kampus;

            $kode = $request->kode;


            $data_simpan = array(
                'id_auth' => $id_auth,
                'kode_pilihan' => $kode,
                'crdt' => date('Y-m-d'),
            );

            DB::table('mhs_perubahan_jrs')->insert($data_simpan);


            $update = array(
                'pmb_status' => 'pindah_jurusan',
                'status' => 2,

            );

            DB::table('mahasiswa')->where('id_auth', $id_auth)->update($update);


            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }
}
