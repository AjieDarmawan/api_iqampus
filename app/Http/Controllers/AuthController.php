<?php


namespace App\Http\Controllers;

use App\Mail\FormEmail;
use App\Models\Jurusan;
use App\Models\KomponenBiaya;
use App\Models\Mahasiswa;
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

class AuthController extends Controller
{
    use utility;
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }


    public function doLoginTesting(Request $request)
    {
        error_reporting(0);
        try {
            $validator = $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (!$validator) {
                return response()->json($this->res_insert("required"));
            }


            // $kampus = $this->ambil_id_kampus();
            $auth = User::with(['user_role', 'user_role.role'])
                ->where("email", $request->email)
                ->where("password", sha1(md5($request->password)))
                ->where("id_kampus", $request->id_kampus)
                ->where("akses", '')
                ->first();

            // echo "<pre>";
            // print_r($auth);
            //    die;


            // dd(
            //     $request->email,
            //     $request->password,
            //     sha1(md5($request)),
            //     $kampus->id,
            //     $auth
            // );
            // if (!Hash::check($request->password, $auth->password)) {
            //     return response()->json($this->res_insert("fatal")); 
            // }

            if ($auth) {
                // echo "<pre>";
                // print_r($auth->user_role->pluck('role.nama')[0]);
                // die;
                $data = JWTAuth::encode(JWTFactory::sub($auth->id)->data([
                    "id" => $auth->id,
                    'id_kampus' => $auth->master_kampus->id,
                    "role" => $auth->user_role->pluck('role.nama')
                ])->make())->get('value');
                // dd($data);

                return response()->json($this->show_data(0, 0, 0, 0, ["token" => $data, "role" => $auth->user_role->pluck('role.nama')[0]]));
            } else {
                return response()->json($this->res_insert("fatal"));
            }
        } catch (Exception $e) {
            // dd($e);
            return response()->json([
                "error" => $e,
                "data" => $this->ambil_subdomain(),
                "kampus" => json_encode($kampus)
            ]);
            return response()->json($this->res_insert("fatal"));
        }
    }

    public function doLogin(Request $request)
    {

        try {
            $validator = $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (!$validator) {
                return response()->json($this->res_insert("required"));
            }


            $kampus = $this->ambil_id_kampus();
            $auth = User::with(['user_role', 'user_role.role'])
                ->where("email", $request->email)
                ->where("password", sha1(md5($request->password)))
                ->where("id_kampus", $kampus->id)
                ->where("akses", '')
                ->first();

            // echo "<pre>";
            // print_r($auth);
            //    die;


            // dd(
            //     $request->email,
            //     $request->password,
            //     sha1(md5($request)),
            //     $kampus->id,
            //     $auth
            // );
            // if (!Hash::check($request->password, $auth->password)) {
            //     return response()->json($this->res_insert("fatal")); 
            // }

            $data = JWTAuth::encode(JWTFactory::sub($auth->id)->data([
                "id" => $auth->id,
                'id_kampus' => $auth->master_kampus->id,
                "role" => $auth->user_role->pluck('role.nama')
            ])->make())->get('value');
            // dd($data);

            return response()->json($this->show_data(0, 0, 0, 0, ["token" => $data, "role" => $auth->user_role->pluck('role.nama')[0]]));
        } catch (Exception $e) {
            // dd($e);
            return response()->json([
                "error" => $e,
                "data" => $this->ambil_subdomain(),
                "kampus" => json_encode($kampus)
            ]);
            return response()->json($this->res_insert("fatal"));
        }
    }

    //auth login pmb
    public function doLoginPmb(Request $request)
    {
        try {
            $validator = $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (!$validator) {
                return response()->json($this->res_insert("required"));
            }

            $kampus = $this->ambil_id_kampus();
            //$kampus = 4;
            $auth = User::with(['user_role', 'user_role.role'])
                ->where("email", $request->email)
                ->where("password", sha1(md5($request->password)))
                //->where("id_kampus",$kampus->id)
                ->where("id_kampus", $kampus->id)
                ->where("akses", 'pmb')
                ->firstOrFail();

            // if (!Hash::check($request->password, $auth->password)) {
            //     return response()->json($this->res_insert("fatal")); 
            // }

            $data = JWTAuth::encode(JWTFactory::sub($auth->id)->data([
                "id" => $auth->id,
                'id_kampus' => $auth->master_kampus->id,
                "role" => $auth->user_role->pluck('role.nama')
            ])->make())->get('value');
            // dd($data);

            return response()->json($this->show_data(0, 0, 0, 0, ["token" => $data, "role" => $auth->user_role->pluck('role.nama'), "akses" => $auth->akses]));
        } catch (Exception $e) {
            // echo "<pre>";
            // print_r($e->getMessage());
            // dd($e->getMessage());
            // return response()->json([
            //     "error"=>$e->getMessage(),
            //     "data"=>$this->ambil_subdomain(),
            //     "kampus"=>json_encode($kampus)
            // ]);
            return response()->json($this->res_insert("fatal"));
        }
    }

    public function decode(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload()->get('data');
            return response()->json($this->show_data(0, 0, 0, 0, $payload));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        } catch (Exception $e) {
            return response()->json($this->res_insert("fatal"));
        }
    }
    public function init(Request $request)
    {
        try {
            $x = $this->ambil_subdomain();
            // $x = "utn.ai.web.id";
            $data = MasterKampus::where('subdomain', $x)->firstOrFail();
            $data->foto_kampus = "http://" . $_SERVER['SERVER_NAME'] . "/public/images/" . $data->foto_kampus;
            // $data->foto_kampus = "http://dev-api.ai.web.id/index.php/public/images/".$data->foto_kampus;
            $data->foto_rektor = "http://" . $_SERVER['SERVER_NAME'] . "/public/images/" . $data->foto_rektor;
            $data->logo_kampus = "http://" . $_SERVER['SERVER_NAME'] . "/public/images/" . $data->logo_kampus;

            //$data->logo_kampus = "http://dev-api.ai.web.id/index.php/public/images/".$data->logo_kampus;
            $data->keterangan =  json_decode($data->keterangan);
            $prodi =  DB::table('m_program_studi')->where('id_kampus', $data->id)->get();

            // echo "<pre>";
            // print_r($prodi);
            // die;

            if ($prodi) {

                foreach ($prodi as $key => $p) {
                    // $ex_prod = explode("|", $p);
                    // $akredit[$key] = $ex_prod[0];


                    $data_prod[] = DB::table('m_jurusan')
                        ->where('kode', $p->kode)
                        ->select('id', 'kode', 'nama', 'jenjang', 'gelar', 'karir', 'komp', 'youtube')
                        ->get();
                }





                foreach ($data_prod as $key1 => $k) {
                    $data_js[] = array(
                        'id' => $k[0]->id,
                        'kode' => $k[0]->kode,
                        'nama' => $k[0]->nama,
                        'jenjang' => $k[0]->jenjang,
                        'gelar' => $k[0]->gelar,
                        'karir' => $k[0]->karir,
                        'komp' => $k[0]->komp,
                        'youtube' => $k[0]->youtube,
                        'akreditasi' => $prodi[$key1]->akre,
                    );
                }


                //$data->prodi2 =  $akredit;
                $data->prodi =  $data_js;
            } else {
                $data->prodi = [];
            }


            return response()->json($this->show_data_object($data));
        } catch (Exception $e) {
            return response()->json($this->null_data());
        }
    }
    public function init_token(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $x = $payload->id_kampus;
            // $x = "utn.ai.web.id";
            $data = MasterKampus::where('id', $x)->firstOrFail();
            $data->foto_kampus = "http://" . $_SERVER['SERVER_NAME'] . "/public/images/" . $data->foto_kampus;
            // $data->foto_kampus = "http://dev-api.ai.web.id/index.php/public/images/".$data->foto_kampus;
            $data->foto_rektor = "http://" . $_SERVER['SERVER_NAME'] . "/public/images/" . $data->foto_rektor;
            $data->logo_kampus = "http://" . $_SERVER['SERVER_NAME'] . "/public/images/" . $data->logo_kampus;

            //$data->logo_kampus = "http://dev-api.ai.web.id/index.php/public/images/".$data->logo_kampus;
            $data->keterangan =  json_decode($data->keterangan);
            $prodi =  DB::table('m_program_studi')->where('id_kampus', $data->id)->get();

            // echo "<pre>";
            // print_r($prodi);
            // die;

            if ($prodi) {

                foreach ($prodi as $key => $p) {
                    // $ex_prod = explode("|", $p);
                    // $akredit[$key] = $ex_prod[0];


                    $data_prod[] = DB::table('m_jurusan')
                        ->where('kode', $p->kode)
                        ->select('id', 'kode', 'nama', 'jenjang', 'gelar', 'karir', 'komp', 'youtube')
                        ->get();
                }





                foreach ($data_prod as $key1 => $k) {
                    $data_js[] = array(
                        'id' => $k[0]->id,
                        'kode' => $k[0]->kode,
                        'nama' => $k[0]->nama,
                        'jenjang' => $k[0]->jenjang,
                        'gelar' => $k[0]->gelar,
                        'karir' => $k[0]->karir,
                        'komp' => $k[0]->komp,
                        'youtube' => $k[0]->youtube,
                        'akreditasi' => $prodi[$key1]->akre,
                    );
                }


                //$data->prodi2 =  $akredit;
                $data->prodi =  $data_js;
            } else {
                $data->prodi = [];
            }


            return response()->json($this->show_data_object($data));
        } catch (Exception $e) {
            return response()->json($this->null_data());
        }
    }
    public function create_auth_admin(Request $request)
    {
        try {
            $validator = $this->validate($request, [
                'email'     => 'required',
                'password'  => 'required',
                'roles'     => 'required',
                "roles.*"   => "required",
            ]);
            // $validator2 = $this->validate($request, [
            //     'email'     => 'email',
            //     'password'  => 'min:6',
            //     'roles'     => 'array|min:1',
            //     "roles.*"   => "string|in:".Role::select('nama')->get()->implode('nama', ', '),
            // ]);
            if (!$validator) {
                return response()->json($this->res_insert("required"));
            }
            // if(!$validator2){
            //     return response()->json($this->res_insert("invalid"));
            // }

            $kampus = $this->ambil_id_kampus();
            $check = User::where(["email" => $request->email, "id_kampus" => $kampus->id])->get()->count();
            if ($check) {
                return response()->json($this->res_insert("unique"));
            }

            DB::transaction(function () use ($request, $kampus) {
                $user = new User();
                $user->email = $request->email;
                $user->password = sha1(md5($request->password));
                $user->email = $request->email;
                $user->id_kampus = $kampus->id;
                $user->save();

                foreach (json_decode($request->roles, true) as $role) {
                    $x = Role::select('id')->where('nama', $role)->firstOrFail();

                    $userRole = new UserRole();
                    $userRole->id_auth = $user->id;
                    $userRole->id_role = $x->id;
                    $userRole->save();
                }
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            // dd($e->getMessage());
            return response()->json($this->res_insert("fatal"));
        }
    }
    public function create_auth_user(Request $request)
    {

        // echo $this->buat_nomor_pmb_pendaftaran(51,"S1CI");
        // die;

        try {
            $validator = $this->validate($request, [
                'nama_lengkap'      => 'required',
                'email'             => 'required',
                'nomor_wa'          => 'required',
                'asal_sekolah'      => 'required',
                'jurusan'           => 'required',
                // 'kelas'           => 'required'
            ]);
            // $validator2 = $this->validate($request, [
            //     'email'             => 'email',
            //     'nomor_wa'          => 'number'
            // ]);
            if (!$validator) {
                return response()->json($this->res_insert("required"));
            }
            // if(!$validator2){
            //     return response()->json($this->res_insert("invalid"));
            // }

            $kampus = $this->ambil_id_kampus();
            $check = User::where(["email" => $request->email, "id_kampus" => $kampus->id])->get()->count();
            if ($check) {
                return response()->json($this->res_insert("unique"));
            }

            DB::transaction(function () use ($request, $kampus) {
                $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()');
                shuffle($seed);
                $rand = '';
                foreach (array_rand($seed, 6) as $k) $rand .= $seed[$k];

                $list_no_pendaftaran = DB::select(DB::raw("select pmb_no_pendaftaran from mahasiswa where id_auth in (select id from auth where akses='pmb' and id_kampus=:id_kampus)"), array('id_kampus' => $kampus->id));

                $user = new User();
                $user->email = $request->email;
                $user->password = sha1(md5($rand)); //Hash::make($rand)
                $user->id_kampus = $kampus->id;
                // $user->nama_lengkap = $request->nama_lengkap;
                $user->akses = "pmb";
                $user->save();

                $x = Role::select('id')->where('nama', 'user')->firstOrFail();

                $userRole = new UserRole();
                $userRole->id_auth = $user->id;
                $userRole->id_role = $x->id;
                $userRole->save();

                // $jurusan = Jurusan::where('kode',$request->jurusan)->firstOrFail();
                $mahasiswa                      = new mahasiswa();
                $mahasiswa->id_auth             = $user->id;
                $mahasiswa->pmb_no_pendaftaran  = $this->buat_nomor_pmb_pendaftaran($kampus->id, $request->jurusan);
                $mahasiswa->asal_sekolah        = $request->asal_sekolah;
                $mahasiswa->jurusan1            = $request->jurusan;


                $mahasiswa->nama            = $request->nama_lengkap;
                $mahasiswa->jenis_kelamin   = "";
                $mahasiswa->tanggal_lahir   = null;
                $mahasiswa->tempat_lahir    = "";
                $mahasiswa->nama_ibu        = "";
                $mahasiswa->agama           = "";
                $mahasiswa->kewarganegaraan = "";
                $mahasiswa->nik             = "";
                $mahasiswa->nisn            = "";
                $mahasiswa->telp            = $request->nomor_wa;
                // $mahasiswa->kelas            = $request->kelas;
                $mahasiswa->kelurahan       = "";
                $mahasiswa->penerima_kps    = "";
                $mahasiswa->kecamatan       = "";
                $mahasiswa->pendidikan_terakhir   = $request->pendidikan_terakhir;
                $mahasiswa->save();

                DB::table('pmb_tahapan_pendaftaran')->insert([
                    "id_auth" => $user->id,
                    "formulir_pendaftaran" => 0,
                    "info_pembayaran" => 0,
                    "lengkapi_berkas" => 0,
                    "jadwal_uji_seleksi" => 0,
                    "info_kelulusan" => 0,
                    'total' => 0,
                ]);

                // $komponen = KomponenBiaya::with( ['master_komponen'=>function($query){ $query->where('nama_komponen','formulir pendaftaran'); }] )->where('id_kampus',$kampus->id)->first();
                Mail::to($request->email)->send(new FormEmail($request->nama, $kampus->nama_kampus, $kampus->subdomain, $request->email, $rand));
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            // dd($e);
            return response()->json(["kampus" => $this->ambil_id_kampus()->id, "log" => $e->getMessage()]);
            return response()->json($this->res_insert("fatal"));
        }
    }
    public function update_auth_user(Request $request)
    {
        error_reporting(0);
        try {
            $payload = JWTAuth::parseToken()->getPayload()->get('data');
            //$user = User::where(["id"=>$payload->id,"id_kampus"=>$payload->id_kampus])->firstOrFail();
            // $kampus = $this->ambil_id_kampus();



            DB::transaction(function () use ($request, $payload) {
                $mahasiswa                    = Mahasiswa::where('id_auth', $payload->id)->first();

                if ($request->type == "formulir") {
                    // insert langsung
                    $validator = $this->validate($request, [
                        'nama'          => 'required',
                        'jenis_kelamin' => 'required',
                        'tanggal_lahir' => 'required',
                        'tempat_lahir'  => 'required',
                        'nama_ibu'      => 'required',
                        'agama'         => 'required',
                        'kewarganegaraan'   => 'required',
                        'nik'               => 'required',
                        'nisn'              => 'required',
                        'jurusan1'              => 'required',
                        'id_gelombang'              => 'required',
                        // 'jalan'             => 'required',
                        // 'telp'              => 'required',
                        // 'dusun'             => 'required',
                        // 'rt'                => 'required',
                        // 'rw'                => 'required',
                        // 'hp'                => 'required',
                        'kelurahan'         => 'required',
                        // 'kode_pos'          => 'required',
                        'penerima_kps'      => 'required',
                        'kecamatan'         => 'required',
                        // 'jenis_tinggal'     => 'required',
                        // 'alat_transformasi' => 'required'
                    ]);

                    if (!$validator) {
                        return response()->json($this->res_insert("required"));
                    }

                    $mahasiswa->nama              = $request->nama;
                    $mahasiswa->jenis_kelamin     = $request->jenis_kelamin;
                    $mahasiswa->tanggal_lahir     = $request->tanggal_lahir;
                    $mahasiswa->tempat_lahir      = $request->tempat_lahir;
                    $mahasiswa->nama_ibu          = $request->nama_ibu;
                    $mahasiswa->agama             = $request->agama;
                    $mahasiswa->save();

                    $auth               = User::find($payload->id);
                    $auth->nama_lengkap = $request->nama;
                    $auth->save();




                    $mahasiswa->kewarganegaraan   = $request->kewarganegaraan;
                    $mahasiswa->nik               = $request->nik;
                    $mahasiswa->nisn              = $request->nisn;
                    $mahasiswa->jurusan1              = $request->jurusan1;
                    $mahasiswa->jurusan2              = $request->jurusan2;
                    $mahasiswa->jurusan3              = $request->jurusan3;
                    $mahasiswa->id_gelombang              = $request->id_gelombang;
                    $mahasiswa->id_kelas              = $request->id_kelas;


                    $mahasiswa->id_kelompok_jenjang              = $request->id_kelompok_jenjang;
                    $mahasiswa->id_tahun_ajar              = $this->cek_tahun_ajar($request->id_gelombang)->id_tahun_ajar;
                    $mahasiswa->id_tahun_ajar_masuk              = $this->cek_tahun_ajar($request->id_gelombang)->id_tahun_ajar;

                    $mahasiswa->npwp              = $request->has('npwp') ? $request->npwp : $mahasiswa->npwp;
                    $mahasiswa->jalan             = $request->has('jalan') ? $request->jalan : $mahasiswa->jalan;
                    $mahasiswa->telp              = $request->has('telp') ? $request->telp : $mahasiswa->telp;
                    $mahasiswa->dusun             = $request->has('dusun') ? $request->dusun : $mahasiswa->dusun;
                    $mahasiswa->rt                = $request->has('rt') ? $request->rt : $mahasiswa->rt;
                    $mahasiswa->rw                = $request->has('rw') ? $request->rw : $mahasiswa->rw;
                    $mahasiswa->hp                = $request->has('hp') ? $request->hp : $mahasiswa->hp;
                    $mahasiswa->kelurahan         = $request->kelurahan;
                    $mahasiswa->kode_pos          = $request->has('kode_pos') ? $request->kode_pos : $mahasiswa->kode_pos;
                    $mahasiswa->penerima_kps      = $request->penerima_kps;
                    $mahasiswa->kecamatan         = $request->kecamatan;
                    $mahasiswa->provinsi         = $request->provinsi;
                    $mahasiswa->kota             = $request->kota;
                    $mahasiswa->jalur_masuk         = $request->jalur_masuk;
                    $mahasiswa->jenis_tinggal     = $request->has('jenis_tinggal') ? $request->jenis_tinggal : $mahasiswa->jenis_tinggal;
                    $mahasiswa->id_alat_transport = $request->has('id_alat_transport') ? $request->id_alat_transport : $mahasiswa->id_alat_transport;


                    $mahasiswa->nik_ayah            = $request->has('nik_ayah') ? $request->nik_ayah : $mahasiswa->nik_ayah;
                    $mahasiswa->nama_ayah           = $request->has('nama_ayah') ? $request->nama_ayah : $mahasiswa->nama_ayah;
                    //$mahasiswa->tanggal_lahir_ayah  = $request->has('tanggal_lahir_ayah') ? $request->tanggal_lahir_ayah : $mahasiswa->tanggal_lahir_ayah;

                    if ($request->tanggal_lahir_ayah) {
                        $tanggal_lahir_ayah = $request->tanggal_lahir_ayah;
                    } else {
                        $tanggal_lahir_ayah = null;
                    }

                    $mahasiswa->tanggal_lahir_ayah  =  $tanggal_lahir_ayah;







                    $mahasiswa->id_jenj_didik_ayah  = $request->has('id_jenj_didik_ayah') ? $request->id_jenj_didik_ayah : $mahasiswa->id_jenj_didik_ayah;
                    $mahasiswa->id_pekerjaan_ayah   = $request->has('id_pekerjaan_ayah') ? $request->id_pekerjaan_ayah : $mahasiswa->id_pekerjaan_ayah;
                    $mahasiswa->id_penghasilan_ayah = $request->has('id_penghasilan_ayah') ? $request->id_penghasilan_ayah : $mahasiswa->id_penghasilan_ayah;
                    $mahasiswa->nik_ibu             = $request->has('nik_ibu') ? $request->nik_ibu : $mahasiswa->nik_ibu;
                    $mahasiswa->nama_ibu            = $request->has('nama_ibu') ? $request->nama_ibu : $mahasiswa->nama_ibu;

                    if ($request->tanggal_lahir_ibu) {
                        $tanggal_lahir_ibu = $request->tanggal_lahir_ibu;
                    } else {
                        $tanggal_lahir_ibu = null;
                    }


                    $mahasiswa->tanggal_lahir_ibu   = $tanggal_lahir_ibu;
                    $mahasiswa->id_jenj_didik_ibu   = $request->has('id_jenj_didik_ibu') ? $request->id_jenj_didik_ibu : $mahasiswa->id_jenj_didik_ibu;
                    $mahasiswa->id_pekerjaan_ibu    = $request->has('id_pekerjaan_ibu') ? $request->id_pekerjaan_ibu : $mahasiswa->id_pekerjaan_ibu;
                    $mahasiswa->id_penghasilan_ibu  = $request->has('id_penghasilan_ibu') ? $request->id_penghasilan_ibu : $mahasiswa->id_penghasilan_ibu;


                    $mahasiswa->nama_wali           = $request->has('nama_wali') ? $request->nama_wali : $mahasiswa->nama_wali;

                    if ($request->tanggal_lahir_wali) {
                        $tanggal_lahir_wali = $request->tanggal_lahir_wali;
                    } else {
                        $tanggal_lahir_wali = null;
                    }


                    $mahasiswa->tanggal_lahir_wali  = $tanggal_lahir_wali;
                    $mahasiswa->id_jenj_didik_wali  = $request->has('id_jenj_didik_wali') ? $request->id_jenj_didik_wali : $mahasiswa->id_jenj_didik_wali;
                    $mahasiswa->id_pekerjaan_wali   = $request->has('id_pekerjaan_wali') ? $request->id_pekerjaan_wali : $mahasiswa->id_pekerjaan_wali;
                    $mahasiswa->id_penghasilan_wali = $request->has('id_penghasilan_wali') ? $request->id_penghasilan_wali : $mahasiswa->id_penghasilan_wali;



                    $mahasiswa->kebutuhan_khusus           = $request->has('kebutuhan_khusus') ? $request->kebutuhan_khusus : $mahasiswa->kebutuhan_khusus;
                    $mahasiswa->save();




                    DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['formulir_pendaftaran' => 1]);

                    $total = DB::select('select sum(formulir_pendaftaran+info_pembayaran+lengkapi_berkas+jadwal_uji_seleksi+info_kelulusan) as total from pmb_tahapan_pendaftaran  where id_auth = "' . $payload->id . '"');

                    DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['total' => $total[0]->total]);
                } else {
                    if ($request->type == "data_mahasiswa") {
                        $validator = $this->validate($request, [
                            'nama'          => 'required',
                            'jenis_kelamin' => 'required',
                            'tanggal_lahir' => 'required',
                            'tempat_lahir'  => 'required',
                            'nama_ibu'      => 'required',
                            'agama'         => 'required',
                        ]);

                        if (!$validator) {
                            return response()->json($this->res_insert("required"));
                        }

                        $mahasiswa->nama              = $request->nama;
                        $mahasiswa->jenis_kelamin     = $request->jenis_kelamin;
                        $mahasiswa->tanggal_lahir     = $request->tanggal_lahir;
                        $mahasiswa->tempat_lahir      = $request->tempat_lahir;
                        $mahasiswa->nama_ibu          = $request->nama_ibu;
                        $mahasiswa->agama             = $request->agama;
                        $mahasiswa->save();

                        $auth               = User::find($payload->id);
                        $auth->nama_lengkap = $request->nama;
                        $auth->save();
                    } else if ($request->type == "info_alamat_mahasiswa") {
                        $validator = $this->validate($request, [
                            'kewarganegaraan'   => 'required',
                            'nik'               => 'required',
                            'nisn'              => 'required',
                            // 'npwp'              => 'required',
                            // 'jalan'             => 'required',
                            // 'telp'              => 'required',
                            // 'dusun'             => 'required',
                            // 'rt'                => 'required',
                            // 'rw'                => 'required',
                            // 'hp'                => 'required',
                            'kelurahan'         => 'required',
                            // 'kode_pos'          => 'required',
                            'penerima_kps'      => 'required',
                            'kecamatan'         => 'required',
                            // 'jenis_tinggal'     => 'required',
                            // 'alat_transformasi' => 'required'
                        ]);

                        if (!$validator) {
                            return response()->json($this->res_insert("required"));
                        }

                        $mahasiswa->kewarganegaraan   = $request->kewarganegaraan;
                        $mahasiswa->nik               = $request->nik;
                        $mahasiswa->nisn              = $request->nisn;
                        $mahasiswa->npwp              = $request->has('npwp') ? $request->npwp : $mahasiswa->npwp;
                        $mahasiswa->jalan             = $request->has('jalan') ? $request->jalan : $mahasiswa->jalan;
                        $mahasiswa->telp              = $request->has('telp') ? $request->telp : $mahasiswa->telp;
                        $mahasiswa->dusun             = $request->has('dusun') ? $request->dusun : $mahasiswa->dusun;
                        $mahasiswa->rt                = $request->has('rt') ? $request->rt : $mahasiswa->rt;
                        $mahasiswa->rw                = $request->has('rw') ? $request->rw : $mahasiswa->rw;
                        $mahasiswa->hp                = $request->has('hp') ? $request->hp : $mahasiswa->hp;
                        $mahasiswa->kelurahan         = $request->kelurahan;
                        $mahasiswa->kode_pos          = $request->has('kode_pos') ? $request->kode_pos : $mahasiswa->kode_pos;
                        $mahasiswa->penerima_kps      = $request->penerima_kps;
                        $mahasiswa->kecamatan         = $request->kecamatan;
                        $mahasiswa->jalur_masuk         = $request->jalur_masuk;
                        $mahasiswa->jenis_tinggal     = $request->has('jenis_tinggal') ? $request->jenis_tinggal : $mahasiswa->jenis_tinggal;
                        $mahasiswa->id_alat_transport = $request->has('id_alat_transport') ? $request->id_alat_transport : $mahasiswa->id_alat_transport;
                        $mahasiswa->save();
                    } else if ($request->type == "info_ortu_mahasiswa") {
                        $mahasiswa->nik_ayah            = $request->has('nik_ayah') ? $request->nik_ayah : $mahasiswa->nik_ayah;
                        $mahasiswa->nama_ayah           = $request->has('nama_ayah') ? $request->nama_ayah : $mahasiswa->nama_ayah;
                        $mahasiswa->tanggal_lahir_ayah  = $request->has('tanggal_lahir_ayah') ? $request->tanggal_lahir_ayah : $mahasiswa->tanggal_lahir_ayah;
                        $mahasiswa->id_jenj_didik_ayah  = $request->has('id_jenj_didik_ayah') ? $request->id_jenj_didik_ayah : $mahasiswa->id_jenj_didik_ayah;
                        $mahasiswa->id_pekerjaan_ayah   = $request->has('id_pekerjaan_ayah') ? $request->id_pekerjaan_ayah : $mahasiswa->id_pekerjaan_ayah;
                        $mahasiswa->id_penghasilan_ayah = $request->has('id_penghasilan_ayah') ? $request->id_penghasilan_ayah : $mahasiswa->id_penghasilan_ayah;
                        $mahasiswa->nik_ibu             = $request->has('nik_ibu') ? $request->nik_ibu : $mahasiswa->nik_ibu;
                        $mahasiswa->nama_ibu            = $request->has('nama_ibu') ? $request->nama_ibu : $mahasiswa->nama_ibu;
                        $mahasiswa->tanggal_lahir_ibu   = $request->has('tanggal_lahir_ibu') ? $request->tanggal_lahir_ibu : $mahasiswa->tanggal_lahir_ibu;
                        $mahasiswa->id_jenj_didik_ibu   = $request->has('id_jenj_didik_ibu') ? $request->id_jenj_didik_ibu : $mahasiswa->id_jenj_didik_ibu;
                        $mahasiswa->id_pekerjaan_ibu    = $request->has('id_pekerjaan_ibu') ? $request->id_pekerjaan_ibu : $mahasiswa->id_pekerjaan_ibu;
                        $mahasiswa->id_penghasilan_ibu  = $request->has('id_penghasilan_ibu') ? $request->id_penghasilan_ibu : $mahasiswa->id_penghasilan_ibu;
                        $mahasiswa->save();
                    } else if ($request->type == "info_wali_mahasiswa") {
                        $mahasiswa->nama_wali           = $request->has('nama_wali') ? $request->nama_wali : $mahasiswa->nama_wali;
                        $mahasiswa->tanggal_lahir_wali  = $request->has('tanggal_lahir_wali') ? $request->tanggal_lahir_wali : $mahasiswa->tanggal_lahir_wali;
                        $mahasiswa->id_jenj_didik_wali  = $request->has('id_jenj_didik_wali') ? $request->id_jenj_didik_wali : $mahasiswa->id_jenj_didik_wali;
                        $mahasiswa->id_pekerjaan_wali   = $request->has('id_pekerjaan_wali') ? $request->id_pekerjaan_wali : $mahasiswa->id_pekerjaan_wali;
                        $mahasiswa->id_penghasilan_wali = $request->has('id_penghasilan_wali') ? $request->id_penghasilan_wali : $mahasiswa->id_penghasilan_wali;
                        $mahasiswa->save();
                    } else if ($request->type == "info_khusus_mahasiswa") {
                        $mahasiswa->kebutuhan_khusus           = $request->has('kebutuhan_khusus') ? $request->kebutuhan_khusus : $mahasiswa->kebutuhan_khusus;
                        $mahasiswa->save();
                    } else {
                        throw new Exception();
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


    public function jurusan_kampus2(Request $request)
    {
        try {
            error_reporting(0);
            // $x = $this->ambil_subdomain();
            $x = "utn.ai.web.id";
            $data = MasterKampus::where('subdomain', $x)->firstOrFail();




            $prodi = DB::table('m_program_studi')
                ->where('id_kampus', 51)
                ->select('*')
                ->get();

            $kelas = $request->kelas;
            $tipe  = $request->tipe;
            $pendidikan_terakhir2  = $request->pendidikan_terakhir;

            if (
                $pendidikan_terakhir2 == 'SMA' || $pendidikan_terakhir2 == 'SMU' || $pendidikan_terakhir2 == 'SMK'
                || $pendidikan_terakhir2 == 'MA' || $pendidikan_terakhir2 == 'MAK' || $pendidikan_terakhir2 == 'D1' || $pendidikan_terakhir2 == 'D2'
            ) {

                $pendidikan_terakhir = 'SMA';
            } else {
                $pendidikan_terakhir = $pendidikan_terakhir2;
            }

            // echo "<pre>";
            // print_r($prodi);

            $data_prodi = [];
            foreach ($prodi as $p) {
                $data_prodi[$p->kode] = $p;
            }

            // echo "<pre>";
            // print_r($data_prodi);

            // die;

            if ($tipe == 'grup') {
                $prodi = $data_prodi;
            } else {
                $prodi = $prodi;
            }



            if ($prodi) {

                foreach ($prodi as $key => $p) {
                    // $ex_prod = explode("|", $p);
                    // $akredit[$key] = $ex_prod[0];




                    if ($pendidikan_terakhir) {
                        $kel_jenjang = DB::table('m_kelompok_jenjang')
                            ->where('id_kelompok_jenjang', $p->id_kelompok_jenjang)
                            ->select('minlulusan', 'id_kelompok_jenjang', 'maxlulusan')
                            ->first();



                        if ($kel_jenjang->maxlulusan == $pendidikan_terakhir) {
                            $data_js_max[] = array(
                                'id' => $this->get_prodi($p->kode)->id,
                                'kode' => $this->get_prodi($p->kode)->kode,
                                'nama' => $this->get_prodi($p->kode)->nama,
                                'jenjang' => $this->get_prodi($p->kode)->jenjang,
                                'gelar' => $this->get_prodi($p->kode)->gelar,
                                'karir' => $this->get_prodi($p->kode)->karir,
                                'komp' => $this->get_prodi($p->kode)->komp,
                                'youtube' => $this->get_prodi($p->kode)->youtube,
                                'akreditasi' => $p->akre,
                                'kelas' => $p->kelas,
                                'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                                'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                            );
                        }

                        // echo count($data_js);
                        //  echo "<pre>";
                        // print_r($kel_jenjang);

                        if (count($data_js_max) == 0) {
                            if ($kel_jenjang->minlulusan == $pendidikan_terakhir) {
                                $data_js_min[] = array(
                                    'id' => $this->get_prodi($p->kode)->id,
                                    'kode' => $this->get_prodi($p->kode)->kode,
                                    'nama' => $this->get_prodi($p->kode)->nama,
                                    'jenjang' => $this->get_prodi($p->kode)->jenjang,
                                    'gelar' => $this->get_prodi($p->kode)->gelar,
                                    'karir' => $this->get_prodi($p->kode)->karir,
                                    'komp' => $this->get_prodi($p->kode)->komp,
                                    'youtube' => $this->get_prodi($p->kode)->youtube,
                                    'akreditasi' => $p->akre,
                                    'kelas' => $p->kelas,
                                    'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                                    'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                                );
                            }
                        }
                        //die;

                        if ($data_js_max) {
                            $data_js = $data_js_max;
                        } else {
                            $data_js = $data_js_min;
                        }
                    } else {
                        $data_js[] = array(
                            'id' => $this->get_prodi($p->kode)->id,
                            'kode' => $this->get_prodi($p->kode)->kode,
                            'nama' => $this->get_prodi($p->kode)->nama,
                            'jenjang' => $this->get_prodi($p->kode)->jenjang,
                            'gelar' => $this->get_prodi($p->kode)->gelar,
                            'karir' => $this->get_prodi($p->kode)->karir,
                            'komp' => $this->get_prodi($p->kode)->komp,
                            'youtube' => $this->get_prodi($p->kode)->youtube,
                            'akreditasi' => $p->akre,
                            'kelas' => $p->kelas,
                            'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                            'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                        );
                    }
                }


                return response()->json($this->show_data_object($data_js));
            }
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->null_data());
        }
    }






    public function jurusan_kampus(Request $request)
    {
        try {
            error_reporting(0);
            // $x = $this->ambil_subdomain();
            $x = "utn.ai.web.id";
            $data = MasterKampus::where('subdomain', $x)->firstOrFail();




            $prodi =  DB::table('m_program_studi')->where('id_kampus', $data->id)->get();

            $kelas = $request->kelas;
            $tipe  = $request->tipe;
            $pendidikan_terakhir2  = $request->pendidikan_terakhir;

            if (
                $pendidikan_terakhir2 == 'SMA' || $pendidikan_terakhir2 == 'SMU' || $pendidikan_terakhir2 == 'SMK'
                || $pendidikan_terakhir2 == 'MA' || $pendidikan_terakhir2 == 'MAK' || $pendidikan_terakhir2 == 'D1' || $pendidikan_terakhir2 == 'D2'
            ) {

                $pendidikan_terakhir = 'SMA';
            } else {
                $pendidikan_terakhir = $pendidikan_terakhir2;
            }

            // echo "<pre>";
            // print_r($prodi);

            $data_prodi = [];
            foreach ($prodi as $p) {
                $data_prodi[$p->kode] = $p;
            }

            // echo "<pre>";
            // print_r($data_prodi);

            // die;

            if ($tipe == 'grup') {
                $prodi = $data_prodi;
            } else {
                $prodi = $prodi;
            }



            if ($prodi) {

                foreach ($prodi as $key => $p) {
                    // $ex_prod = explode("|", $p);
                    // $akredit[$key] = $ex_prod[0];




                    if ($pendidikan_terakhir) {
                        $kel_jenjang = DB::table('m_kelompok_jenjang')
                            ->where('id_kelompok_jenjang', $p->id_kelompok_jenjang)
                            ->select('minlulusan', 'id_kelompok_jenjang', 'maxlulusan')
                            ->first();



                        if ($kel_jenjang->maxlulusan == $pendidikan_terakhir) {
                            $data_js_max[] = array(
                                'id' => $this->get_prodi($p->kode)->id,
                                'kode' => $this->get_prodi($p->kode)->kode,
                                'nama' => $this->get_prodi($p->kode)->nama,
                                'jenjang' => $this->get_prodi($p->kode)->jenjang,
                                'gelar' => $this->get_prodi($p->kode)->gelar,
                                'karir' => $this->get_prodi($p->kode)->karir,
                                'komp' => $this->get_prodi($p->kode)->komp,
                                'youtube' => $this->get_prodi($p->kode)->youtube,
                                'akreditasi' => $p->akre,
                                'kelas' => $p->kelas,
                                'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                                'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                            );
                        }

                        // echo count($data_js);
                        //  echo "<pre>";
                        // print_r($kel_jenjang);

                        if (count($data_js_max) == 0) {
                            if ($kel_jenjang->minlulusan == $pendidikan_terakhir) {
                                $data_js_min[] = array(
                                    'id' => $this->get_prodi($p->kode)->id,
                                    'kode' => $this->get_prodi($p->kode)->kode,
                                    'nama' => $this->get_prodi($p->kode)->nama,
                                    'jenjang' => $this->get_prodi($p->kode)->jenjang,
                                    'gelar' => $this->get_prodi($p->kode)->gelar,
                                    'karir' => $this->get_prodi($p->kode)->karir,
                                    'komp' => $this->get_prodi($p->kode)->komp,
                                    'youtube' => $this->get_prodi($p->kode)->youtube,
                                    'akreditasi' => $p->akre,
                                    'kelas' => $p->kelas,
                                    'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                                    'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                                );
                            }
                        }
                        //die;

                        if ($data_js_max) {
                            $data_js = $data_js_max;
                        } else {
                            $data_js = $data_js_min;
                        }
                    } else {
                        $data_js[] = array(
                            'id' => $this->get_prodi($p->kode)->id,
                            'kode' => $this->get_prodi($p->kode)->kode,
                            'nama' => $this->get_prodi($p->kode)->nama,
                            'jenjang' => $this->get_prodi($p->kode)->jenjang,
                            'gelar' => $this->get_prodi($p->kode)->gelar,
                            'karir' => $this->get_prodi($p->kode)->karir,
                            'komp' => $this->get_prodi($p->kode)->komp,
                            'youtube' => $this->get_prodi($p->kode)->youtube,
                            'akreditasi' => $p->akre,
                            'kelas' => $p->kelas,
                            'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                            'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                        );
                    }
                }


                return response()->json($this->show_data_object($data_js));
            }
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->null_data());
        }
    }


    function pilihjenjang(Request $req)
    {
        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {


                error_reporting(0);
                // $x = $this->ambil_subdomain();
                // $x = "utn.ai.web.id";
                $data = MasterKampus::where('id', $payload->id_kampus)->firstOrFail();


                $prodi =  DB::table('m_program_studi')->where('id_kampus', $payload->id_kampus)->get();

                $kelas = $req->kelas;
                $tipe  = $req->tipe;
                $pendidikan_terakhir2  = $req->pendidikan_terakhir;

                //  if($pendidikan_terakhir2=='SMA' || $pendidikan_terakhir2=='SMU' || $pendidikan_terakhir2=='SMK' 
                //     || $pendidikan_terakhir2=='MA' || $pendidikan_terakhir2=='MAK'|| $pendidikan_terakhir2=='D1' || $pendidikan_terakhir2=='D2'){

                //         $pendidikan_terakhir = 'SMA';
                //  }else{
                //     $pendidikan_terakhir = $pendidikan_terakhir2;
                //  }

                // // echo "<pre>";
                // // print_r($prodi);

                $data_prodi = [];
                foreach ($prodi as $p) {
                    $data_prodi[$p->id_kelompok_jenjang] = $p;
                }




                // echo "<pre>";
                // print_r($data_prodi);


                foreach ($data_prodi as $p) {
                    $data_json[] = array(
                        'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                        'jenjang' => $this->get_kelompok_jenjang($p->id_kelompok_jenjang)->maxlulusan,
                        'nourut' => $this->get_kelompok_jenjang($p->id_kelompok_jenjang)->nourut_pendidikan,
                    );
                }


                foreach ($data_json as $key => $row) {
                    $id_kelompok_jenjang[$key]  = $row['id_kelompok_jenjang'];
                    $jenjang[$key] = $row['jenjang'];
                    $nourut[$key] = $row['nourut'];
                }

                array_multisort($nourut, SORT_ASC, $data_json);

                // echo "<pre>";
                // print_r($nourut);

                echo json_encode($this->show_data_object($data_json));
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

    function pilihkelas(Request $req)
    {
        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_kampus = $payload->id_kampus;


            error_reporting(0);
            // $x = $this->ambil_subdomain();
            // $x = "utn.ai.web.id";
            $data = MasterKampus::where('id', $id_kampus)->firstOrFail();


            $prodi =  DB::table('m_program_studi')->where('id_kampus', $id_kampus)->get();

            $kelas = $req->kelas;



            $id_kelompok_jenjang = $req->id_kelompok_jenjang;




            // $tipe  = $request->tipe;
            // $pendidikan_terakhir2  = $request->pendidikan_terakhir;



            // echo "<pre>";
            // print_r($prodi);

            // die;

            $data_prodi = [];
            foreach ($prodi as $p) {
                $data_prodi[$id_kelompok_jenjang][$p->id_kelas] = $p;
            }




            // echo "<pre>";
            // print_r($data_prodi);
            // die;

            foreach ($data_prodi as $p) {
                $data_json = $p;
            }


            foreach ($data_json as  $row) {

                $data_js[] = array(
                    'id_kelas' => $row->id_kelas,
                    'kelas' => $this->kelas($id_kampus, $row->id_kelas),
                    'id_kelompok_jenjang' => $row->id_kelompok_jenjang,
                );
            }

            //   array_multisort($nourut, SORT_ASC,$data_json);

            // echo "<pre>";
            // print_r($nourut);

            return response()->json($this->show_data_object($data_js));
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


    public function jurusan_kampus_kelas_jenjang(Request $request)
    {
        try {
            error_reporting(0);
            // $x = $this->ambil_subdomain();
            $x = "utn.ai.web.id";
            $data = MasterKampus::where('subdomain', $x)->firstOrFail();
            //  $data->foto_kampus = "http://".$_SERVER['SERVER_NAME']."/public/images/".$data->foto_kampus;
            // // $data->foto_kampus = "http://dev-api.ai.web.id/index.php/public/images/".$data->foto_kampus;
            //  $data->foto_rektor = "http://".$_SERVER['SERVER_NAME']."/public/images/".$data->foto_rektor;
            //  $data->logo_kampus = "http://".$_SERVER['SERVER_NAME']."/public/images/".$data->logo_kampus;

            //  //$data->logo_kampus = "http://dev-api.ai.web.id/index.php/public/images/".$data->logo_kampus;
            //  $data->keterangan =  json_decode($data->keterangan);
            // $prodi =  json_decode($data->prodi);



            $prodi =  DB::table('m_program_studi')->where('id_kampus', $data->id)->get();

            $id_kelas = $request->id_kelas;
            $tipe  = $request->tipe;
            $pendidikan_terakhir2  = $request->pendidikan_terakhir;

            if (
                $pendidikan_terakhir2 == 'SMA' || $pendidikan_terakhir2 == 'SMU' || $pendidikan_terakhir2 == 'SMK'
                || $pendidikan_terakhir2 == 'MA' || $pendidikan_terakhir2 == 'MAK' || $pendidikan_terakhir2 == 'D1' || $pendidikan_terakhir2 == 'D2'
            ) {

                $pendidikan_terakhir = 'SMA';
            } else {
                $pendidikan_terakhir = $pendidikan_terakhir2;
            }

            // echo "<pre>";
            // print_r($prodi);

            $data_prodi = [];
            foreach ($prodi as $p) {
                $data_prodi[$p->kode] = $p;
            }

            // echo "<pre>";
            // print_r($data_prodi);

            // die;

            if ($tipe == 'grup') {
                $prodi = $data_prodi;
            } else {
                $prodi = $prodi;
            }



            if ($prodi) {

                foreach ($prodi as $key => $p) {
                    // $ex_prod = explode("|", $p);
                    // $akredit[$key] = $ex_prod[0];





                    $kel_jenjang = DB::table('m_kelompok_jenjang')
                        ->where('id_kelompok_jenjang', $p->id_kelompok_jenjang)
                        ->select('minlulusan', 'id_kelompok_jenjang', 'maxlulusan')
                        ->first();



                    if ($kel_jenjang->maxlulusan == $pendidikan_terakhir) {
                        $data_js_max[$p->id_kelas][] = array(
                            'id' => $this->get_prodi($p->kode)->id,
                            'kode' => $this->get_prodi($p->kode)->kode,
                            'nama' => $this->get_prodi($p->kode)->nama,
                            'jenjang' => $this->get_prodi($p->kode)->jenjang,
                            // 'gelar' => $this->get_prodi($p->kode)->gelar,
                            // 'karir' => $this->get_prodi($p->kode)->karir,
                            // 'komp' => $this->get_prodi($p->kode)->komp,
                            // 'youtube' => $this->get_prodi($p->kode)->youtube,
                            'akreditasi' => $p->akre,
                            'kelas' => $p->kelas,
                            'id_kelas' => $p->id_kelas,
                            'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                            'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                        );
                    }

                    // echo count($data_js);
                    //  echo "<pre>";
                    // print_r($kel_jenjang);

                    if (count($data_js_max) == 0) {
                        if ($kel_jenjang->minlulusan == $pendidikan_terakhir) {
                            $data_js_min[$p->id_kelas][] = array(
                                'id' => $this->get_prodi($p->kode)->id,
                                'kode' => $this->get_prodi($p->kode)->kode,
                                'nama' => $this->get_prodi($p->kode)->nama,
                                'jenjang' => $this->get_prodi($p->kode)->jenjang,
                                // 'gelar' => $this->get_prodi($p->kode)->gelar,
                                // 'karir' => $this->get_prodi($p->kode)->karir,
                                // 'komp' => $this->get_prodi($p->kode)->komp,
                                // 'youtube' => $this->get_prodi($p->kode)->youtube,
                                'akreditasi' => $p->akre,
                                'kelas' => $p->kelas,
                                'id_kelas' => $p->id_kelas,
                                'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
                                'nama_kelompok_jenjang' => $this->kelompok_jenjang($p->id_kelompok_jenjang),
                            );
                        }
                    }
                    //die;

                    if ($data_js_max) {
                        $data_js = $data_js_max[$id_kelas];
                    } else {
                        $data_js = $data_js_min[$id_kelas];
                    }
                }


                return response()->json($this->show_data_object($data_js));
            }
        } catch (Exception $e) {
            return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
            //return response()->json($this->null_data());
        }
    }

    function cek_gelombang()
    {

        error_reporting(0);
        $x = $this->ambil_subdomain();
        // $x = "utn.ai.web.id";
        $data = MasterKampus::where('subdomain', $x)->firstOrFail();

        $id_kampus = $data->id;

        $gelombang = DB::table('pmb_gelombang')
            ->where('tgl_mulai', '<=', date('Y-m-d'))
            ->where('tgl_selesai', '>=', date('Y-m-d'))
            ->where('id_kampus', $id_kampus)
            ->first();


        echo json_encode($this->show_data_object($gelombang));

        // echo "<pre>";
        // print_r($data->id);
        // die;
    }
}
