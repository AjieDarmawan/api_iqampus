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

class KeuanganAdminController extends Controller
{
    protected $jwt;
    use utility;


    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }


    function jenis_tagihan(Request $req)
    {
        try {

            error_reporting(0);

            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');


                $id_tahun_ajars = $req->id_tahun_ajar;


                $jenis_tagihan = \DB::table('m_komponen')
                    ->where('id_kampus', $id_kampus)
                    ->where('id_tahun_ajar', $id_tahun_ajars)
                    ->where('status', 1)
                    ->get();








                foreach ($jenis_tagihan as $d) {
                    $data_json[] = array(
                        "id_komponen" => $d->id_komponen,
                        "nama_komponen" => $d->nama_komponen,
                        "kode" => $d->kode,
                        "kelompok" => $d->kelompok,
                        "frekuensi" => $d->frekuensi,
                        "id_event" => $d->id_event,
                        "name_status" => $d->name_status,
                        "id_kampus" => $d->id_kampus,
                        "status" => $d->status,
                        "nama_tahun_ajar" => $this->tahun_ajar($d->id_tahun_ajar)->tahun_ajar,
                    );
                }


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

    public function list_jenis_tagihan(Request $req)
    {


        try {

            error_reporting(0);

            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $pages = $req->get('pages');

                $searchs = $req->get('search');
                $id_tahun_ajars = $req->get('id_tahun_ajar');

                $limits = $req->get('limit');

                if ($searchs) {
                    $search = $searchs;
                } else {
                    $search = '';
                }

                if ($id_tahun_ajars) {
                    $id_tahun_ajar = $id_tahun_ajars;
                } else {
                    $id_tahun_ajar = '';
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


                if ($id_tahun_ajars) {
                    $jenis_tagihan = \DB::table('m_komponen')
                        ->where('id_kampus', $id_kampus)
                        ->where('id_tahun_ajar', $id_tahun_ajar)

                        ->where('nama_komponen', 'LIKE', '%' . $search . '%')
                        ->where('status', 1)
                        ->paginate($limit, ['*'], 'page', $page);
                } else {
                    $jenis_tagihan = \DB::table('m_komponen')
                        ->where('id_kampus', $id_kampus)
                        //->where('id_tahun_ajar',$id_tahun_ajar)

                        ->where('nama_komponen', 'LIKE', '%' . $search . '%')
                        ->where('status', 1)
                        ->paginate($limit, ['*'], 'page', $page);
                }




                $dari = $jenis_tagihan->currentPage();
                $hingga = $jenis_tagihan->lastPage();
                $totaldata = $jenis_tagihan->total();
                $totalhalaman = $jenis_tagihan->count();
                $data = $jenis_tagihan->items();


                foreach ($jenis_tagihan as $d) {
                    $data_json[] = array(
                        "id_komponen" => $d->id_komponen,
                        "nama_komponen" => $d->nama_komponen,
                        "kode" => $d->kode,
                        "kelompok" => $d->kelompok,
                        "frekuensi" => $d->frekuensi,
                        "id_event" => $d->id_event,
                        "name_status" => $d->name_status,
                        "id_kampus" => $d->id_kampus,
                        "status" => $d->status,
                        "nama_tahun_ajar" => $this->tahun_ajar($d->id_tahun_ajar)->tahun_ajar,
                    );
                }


                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
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

    function list_detail_jenis_tagihan(Request $req)
    {
        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                $id_tahun_ajar = $req->id_tahun_ajar;



                $m_komponen = DB::table('m_komponen')->where('id_kampus', $id_kampus)->where('id_tahun_ajar', $id_tahun_ajar)->select('id_komponen', 'nama_komponen')->get();






                echo json_encode($this->show_data_object($m_komponen));
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

    public function tambah_jenis_tagihan(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                $jenis_tagihan  = $req->jenis_tagihan;
                $nama_kelompok       = $req->kelompok;
                $frekuensi      = $req->frekuensi;
                $id_tahun_ajar      = $req->id_tahun_ajar;
                $id_event      = $req->id_event;

                // if($kelompok=='formulir'){
                //     $name_status = 'formulir';
                // }elseif($kelompok=='pendaftaran'){
                //     $name_status = 'herreg';
                // }else{
                //      $name_status = 'biaya_kuliah';
                // }

                $kelompok = DB::table('m_kelompok_komponen')->where('nama_kelompok', $nama_kelompok)->first();


                // echo $name_status;
                // die;


                $data_simpan = array(
                    'id_kampus' => $id_kampus,
                    'kelompok' => $kelompok->nama_kelompok,
                    'frekuensi' => $frekuensi,
                    'nama_komponen' => $jenis_tagihan,
                    'status' => 1,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'crdt' => date('Y-m-d H:i:s'),
                    'name_status' => $kelompok->name_status,
                    'id_event' => $id_event,
                );

                DB::table('m_komponen')->insert($data_simpan);



                echo json_encode($this->res_insert("sukses"));
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


    public function edit_jenis_tagihan(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                $jenis_tagihan  = $req->jenis_tagihan;
                $nama_kelompok       = $req->kelompok;
                $frekuensi      = $req->frekuensi;
                $id_tahun_ajar      = $req->id_tahun_ajar;

                $id_komponen    = $req->id_komponen;

                // if($kelompok=='formulir'){
                //     $name_status = 'formulir';
                // }elseif($kelompok=='pendaftaran'){
                //     $name_status = 'herreg';
                // }else{
                //      $name_status = 'biaya_kuliah';
                // }

                // // echo $name_status;
                // // die;

                $kelompok = DB::table('m_kelompok_komponen')->where('nama_kelompok', $nama_kelompok)->first();


                $data_simpan = array(
                    'id_kampus' => $id_kampus,
                    'kelompok' => $kelompok->nama_kelompok,
                    'frekuensi' => $frekuensi,
                    'nama_komponen' => $jenis_tagihan,
                    'status' => 1,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'crdt' => date('Y-m-d H:i:s'),
                    'name_status' => $kelompok->name_status,
                );

                DB::table('m_komponen')->where('id_komponen', $id_komponen)->update($data_simpan);



                echo json_encode($this->res_insert("sukses"));
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

    function list_penentuan_tarif(Request $req)
    {
        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                error_reporting(0);
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                $id_tahun_ajar = $req->get('id_tahun_ajar');
                $id_gelombang = $req->get('id_gelombang');
                $kelas = $req->get('kelas');
                $kelompok_jenjang = $req->get('id_kelompok_jenjang');
                $pages = $req->get('pages');
                $search = $req->get('search');
                $limits = $req->get('limit');

                $id_gelombang = $req->get('id_gelombang');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }


                if ($pages) {
                    $page = $pages - 1;
                    $pagess = $pages;
                } else {
                    $page = 0;
                    $pagess = 1;
                }

                $offset = $limit * $page;






                if ($id_tahun_ajar) {
                    $where_tahun_ajar = "and id_tahun_ajar = '" . $id_tahun_ajar . "'";
                } else {
                    $where_tahun_ajar = "";
                }


                if ($kelompok_jenjang) {
                    $where_kelompok_jenjang = "and id_kelompok_jenjang = '" . $kelompok_jenjang . "'";
                } else {
                    $where_kelompok_jenjang = "";
                }


                if ($kelas) {
                    $where_kelas = "and id_kelas = '" . $kelas . "'";
                } else {
                    $where_kelas = "";
                }

                if ($id_gelombang) {
                    $where_id_gelombang = "and id_gelombang = '" . $id_gelombang . "'";
                } else {
                    $where_id_gelombang = "";
                }



                $KomponenBiaya = DB::select(
                    'select id_tahun_ajar,kode_jurusan,id_gelombang,id_kelas,id_kelompok_jenjang from komponen_biaya as b 
                where  id_kampus = ' . $id_kampus . '  
                 ' . $where_tahun_ajar . '
                  ' . $where_kelompok_jenjang . '
                  ' . $where_kelas . '
                  ' . $where_id_gelombang . '
               
                 group by id_tahun_ajar,kode_jurusan,id_gelombang,id_kelas,id_kelompok_jenjang order by id_tahun_ajar DESC LIMIT ' . $limit . ' OFFSET ' . $offset . '
                 '
                );


                // echo "<pre>";
                // print_r($KomponenBiaya);
                // die;



                $count = DB::select('select count(id_komponen_biaya) as total from komponen_biaya where id_kampus = 
                ' . $id_kampus . ' 
                ' . $where_tahun_ajar . ' 
                 ' . $where_kelompok_jenjang . '
                 ' . $where_kelas . '
                 ' . $where_id_gelombang . '
                group by id_tahun_ajar,kode_jurusan,id_gelombang,id_kelas,id_kelompok_jenjang');

                // echo "<pre>";
                // print_r(count($count));
                // die;



                $dari = (int)$pagess;
                $totaldata = count($count);
                $hingga = ceil($totaldata / $limit);
                $totalhalaman = count($KomponenBiaya);



                foreach ($KomponenBiaya as $k) {
                    $data_json[] = array(
                        "id_tahun_ajar" => $k->id_tahun_ajar,
                        "tahun_ajar" => $this->tahun_ajar($k->id_tahun_ajar)->tahun_ajar,
                        "kode_jurusan" => $k->kode_jurusan,
                        "nama_jurusan" => $this->nama_prodi($k->kode_jurusan),
                        'id_gelombang' => $k->id_gelombang,
                        "nama_gelombang" => $this->cek_tahun_ajar($k->id_gelombang)->nama_gelombang,

                        "kelas" => $this->kelas($id_kampus, $k->id_kelas),
                        'id_kelas' => $k->id_kelas,
                        "id_kelompok_jenjang" => $k->id_kelompok_jenjang,
                        "nama_kelompok_jenjang" => $this->kelompok_jenjang($k->id_kelompok_jenjang),
                        // 'nama_komponen'=>$k->nama_komponen,
                    );
                }

                error_reporting(0);
                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
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


    public function buat_tarif_tagihan(Request $req)
    {




        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {
                error_reporting(0);
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                $id_tahun_ajar      = $req->id_tahun_ajar;
                $id_gelombang       = $req->id_gelombang;
                $kelas              = $req->kelas;
                $id_kelompok_jenjang   = $req->id_kelompok_jenjang;



                // $kode_prodi_last        = $req->kode_prodi;
                // $jenis_tagihan_last = $req->jenis_tagihan;
                // $nominal_last = $req->nominal;
                // $jenis_pembayaran_last = $req->jenis_pembayaran;
                // $cicilan_last = $req->cicilan;

                $kode_prodi        = $req->kode_prodi;
                $jenis_tagihan = $req->jenis_tagihan;
                $nominal = $req->nominal;
                $jenis_pembayaran = $req->jenis_pembayaran;
                $cicilan = $req->cicilan;


                // $kode_prodi    = explode(',', $kode_prodi_last);
                // $jenis_tagihan = explode(',', $jenis_tagihan_last);
                // $nominal       = explode(',', $nominal_last);
                // $jenis_pembayaran  = explode(',', $jenis_pembayaran_last);
                // $cicilan  = explode(',', $cicilan_last);




                foreach ($cicilan as $key => $c) {

                    if ($c >= 2) {

                        $datanya_cicilan = $this->nourut($c);

                        foreach ($datanya_cicilan as $ci) {
                            $datanya[] = array(
                                'id_kampus' => $id_kampus,
                                'id_komponen' => $jenis_tagihan[$key],
                                'id_tahun_ajar' => $id_tahun_ajar,
                                'biaya' => $nominal,
                                // 'kode_prodi'=>$kode_prodi[$key],
                                'id_gelombang' => $id_gelombang,
                                'kelas' => $kelas,
                                'cicilan' => $cicilan,
                                'jenis_pembayaran' => $jenis_pembayaran[$key],
                                'id_kelompok_jenjang' => $id_kelompok_jenjang,
                                'status' => 1,
                                'crdt' => date('Y-m-d H:i:s'),
                                'cicilan' => $ci,
                                'biaya' => $nominal[$key] / $c,
                            );
                        }

                        $data_cicilan[] = array(

                            'biaya' => $nominal[$key],
                            'cicilan' => $c,
                            'key' => $key,
                            'listdata' => $datanya

                        );
                    }
                }


                // foreach($data_cicilan as $key => $c){

                // }

                // echo "<pre>";
                // print_r($data_cicilan);

                //  echo json_encode($datanya);
                // die;




                foreach ($jenis_tagihan as $key => $j) {

                    if ($cicilan[$key] <= 1) {
                        $data_tetap[] = array(
                            'id_kampus' => $id_kampus,
                            'id_komponen' => $jenis_tagihan[$key],
                            'id_tahun_ajar' => $id_tahun_ajar,
                            'biaya' => $nominal[$key],
                            // 'kode_prodi'=>$kode_prodi[$key],
                            'id_gelombang' => $id_gelombang,
                            'kelas' => $kelas,
                            'cicilan' => $cicilan[$key],
                            'jenis_pembayaran' => $jenis_pembayaran[$key],
                            'id_kelompok_jenjang' => $id_kelompok_jenjang,
                            'status' => 1,
                            'crdt' => date('Y-m-d H:i:s'),

                        );
                    }
                }


                // echo "<pre>";
                // print_r($data_simpan);

                //echo json_encode($data_tetap);

                $data_fix2 = (array) array_merge($data_tetap, $datanya);

                if ($data_fix2) {
                    $data_fix = $data_fix2;
                } elseif ($data_tetap) {
                    $data_fix = $data_tetap;
                } else {
                    $data_fix = $datanya;
                }

                // echo json_encode($data_fix);
                // die;

                // echo "<pre>";
                // print_r($data_tetap);

                // die;


                foreach ($kode_prodi as $key => $k) {
                    foreach ($data_fix as $f) {
                        $data_simpan[] = array(
                            'id_kampus' => $f['id_kampus'],
                            'id_komponen' => $f['id_komponen'],
                            'id_tahun_ajar' => $f['id_tahun_ajar'],
                            'biaya' => $f['biaya'],
                            'id_gelombang' => $f['id_gelombang'],
                            'id_kelas' => $f['kelas'],
                            'cicilan' => $f['cicilan'],
                            'jenis_pembayaran' => $f['jenis_pembayaran'],
                            'id_kelompok_jenjang' => $f['id_kelompok_jenjang'],
                            'status' => 1,
                            'crdt' => date('Y-m-d H:i:s'),
                            'kode_jurusan' => $k,
                        );
                    }
                }

                // echo json_encode($data_simpan);
                // die;


                DB::table('komponen_biaya')->insert($data_simpan);



                echo json_encode($this->res_insert("sukses"));
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

    function list_edit_buat_tarif(Request $req)
    {

        try {
            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $kode_jurusan = $req->kode_jurusan;
                $id_kelas = $req->kelas;
                $id_tahun_ajar = $req->id_tahun_ajar;
                $id_gelombang = $req->id_gelombang;
                $id_kelompok_jenjang = $req->id_kelompok_jenjang;
                //  $kode_jurusan = $req->kode_jurusan;

                // $jenis_tagihan = DB::table('komponen_biaya')
                //     ->select('id_komponen', 'id_tahun_ajar', 'id_gelombang', 'id_kelas', 'jenis_pembayaran')
                //     ->where('kode_jurusan', $kode_jurusan)
                //     ->where('id_kampus', $id_kampus)
                //     ->where('id_kelas', $kelas)
                //     ->where('id_tahun_ajar', $id_tahun_ajar)
                //     ->where('id_gelombang', $id_gelombang)
                //     ->where('id_kelompok_jenjang', $id_kelompok_jenjang)
                //     ->groupBy('id_komponen', 'id_tahun_ajar', 'id_gelombang', 'id_kelas', 'jenis_pembayaran')
                //     ->get();

                $jenis_tagihan = DB::table('m_komponen')
                    ->select('*')
                    ->where('id_kampus', $id_kampus)
                    ->where('id_tahun_ajar', $id_tahun_ajar)
                    ->get();




                // echo "<pre>";
                // print_r($jenis_tagihan);
                // die;


                foreach ($jenis_tagihan as $j) {

                    $cicilan = DB::select("select max(k.cicilan) as cicilan from komponen_biaya as k where k.id_komponen=" . $j->id_komponen . " and k.kode_jurusan = '" . $kode_jurusan . "' and k.id_kampus =  " . $id_kampus . "");

                    $biaya = DB::select("select sum(k.biaya) as biaya from komponen_biaya as k where k.id_komponen=" . $j->id_komponen . " and k.kode_jurusan = '" . $kode_jurusan . "' and k.id_kampus =  " . $id_kampus . "");


                    $datanya = DB::table('komponen_biaya as k')
                        ->select('k.jenis_pembayaran', 'k.id_komponen_biaya')
                        ->where('k.id_komponen', $j->id_komponen)
                        ->where('k.id_kampus', $id_kampus)
                        ->where('k.kode_jurusan', $kode_jurusan)
                        ->first();

                    $status = DB::table('payment_detail as k')
                        ->select('id')
                        ->where('k.id_komponen_biaya', $datanya->id_komponen_biaya)
                        ->first();


                    $cek_generate = DB::table('tagihan_mahasiswa as k')
                        ->join('komponen_biaya as kb', 'kb.id_komponen_biaya', '=', 'k.id_komponen_biaya')
                        ->join('m_komponen as m', 'm.id_komponen', '=', 'kb.id_komponen')
                        ->select('k.id_tagihan_mahasiswa')
                        // ->where('k.id_komponen_biaya', $datanya->id_komponen_biaya)
                        ->where('k.kode_jurusan', $kode_jurusan)
                        ->where('kb.id_kampus', $id_kampus)
                        ->where('k.id_kelas', $id_kelas)
                        ->where('k.id_tahun_ajar', $id_tahun_ajar)
                        ->where('kb.id_gelombang', $id_gelombang)
                        ->where('kb.id_kelompok_jenjang', $id_kelompok_jenjang)
                        ->where('m.id_komponen', $datanya->id_komponen)
                        ->first();

                    if ($status->id) {
                        $status_cek = 1;
                    } else {
                        if ($cek_generate->id_tagihan_mahasiswa) {
                            $status_cek = 1;
                        } else {
                            $status_cek = 0;
                        }
                    }


                    $data_json[] = array(
                        'id_komponen' => $j->id_komponen,
                        'nama_komponen' => $this->nama_komponen($j->id_komponen),
                        'id_tahun_ajar' => $this->tahun_ajar($id_tahun_ajar)->tahun_ajar,
                        'id_gelombang' => $this->cek_tahun_ajar($id_gelombang)->nama_gelombang,
                        'id_kelas' => $id_kelas,
                        'kelas' => $this->kelas($id_kampus, $id_kelas),
                        'jenis_pembayaran' => $datanya->jenis_pembayaran,
                        'cicilan' => $cicilan[0]->cicilan,
                        'biaya' => round($biaya[0]->biaya),
                        'status' => $status_cek,


                    );
                }

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


    function simpan_edit_penentuan_tarif(Request $req)
    {
        //$data_json = [];


        try {
            // error_reporting(0);
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {
                error_reporting(0);


                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $kode_jurusan = $req->kode_jurusan;
                $id_kelas = $req->kelas;
                $id_tahun_ajar = $req->id_tahun_ajar;
                $id_gelombang = $req->id_gelombang;
                $id_kelompok_jenjang = $req->id_kelompok_jenjang;


                $jenis_tagihan = $req->jenis_tagihan;
                $nominal       = $req->nominal;
                $jenis_pembayaran = $req->jenis_pembayaran;
                $cicilan = $req->cicilan;

                // foreach($jenis_tagihan as $keyy => $j){
                //         $data_request[] = array(
                //             'id_komponen'=>$j,
                //             'cicilan'=>$cicilan[$keyy],
                //             'biaya'=>$nominal[$keyy],
                //             'jenis_pembayaran'=>$jenis_pembayaran[$keyy],

                //         );
                // }        






                $hapus = DB::table('komponen_biaya')
                    ->select('id_komponen_biaya', 'id_komponen', 'id_tahun_ajar', 'id_gelombang', 'id_kelas', 'jenis_pembayaran')
                    ->where('kode_jurusan', $kode_jurusan)
                    ->where('id_kampus', $id_kampus)
                    ->where('id_kelas', $id_kelas)
                    ->where('id_tahun_ajar', $id_tahun_ajar)
                    ->where('id_gelombang', $id_gelombang)
                    ->where('id_kelompok_jenjang', $id_kelompok_jenjang)
                    ->whereIn('id_komponen', $jenis_tagihan)

                    ->get();

                foreach ($hapus as $h) {
                    DB::table('komponen_biaya')->where('id_komponen_biaya', $h->id_komponen_biaya)->delete();
                }





                // insert
                foreach ($cicilan as $key => $c) {

                    if ($c >= 2) {

                        $datanya_cicilan = $this->nourut($c);


                        foreach ($datanya_cicilan as $ci) {
                            $datanya[] = array(
                                'id_kampus' => $id_kampus,
                                'id_komponen' => $jenis_tagihan[$key],
                                'id_tahun_ajar' => $id_tahun_ajar,
                                'biaya' => $nominal,
                                // 'kode_prodi'=>$kode_prodi[$key],
                                'id_gelombang' => $id_gelombang,
                                'id_kelas' => $id_kelas,
                                'cicilan' => $cicilan,
                                'jenis_pembayaran' => $jenis_pembayaran[$key],
                                'id_kelompok_jenjang' => $id_kelompok_jenjang,
                                'status' => 1,
                                'crdt' => date('Y-m-d H:i:s'),
                                'kode_jurusan' => $kode_jurusan,
                                'cicilan' => $ci,
                                'biaya' => $nominal[$key] / $c,
                            );
                        }

                        $data_cicilan[] = array(

                            'biaya' => $nominal[$key],
                            'cicilan' => $c,
                            'key' => $key,
                            'listdata' => $datanya

                        );
                    }
                }

                foreach ($jenis_tagihan as $key => $j) {

                    if ($cicilan[$key] <= 1) {
                        $data_tetap[] = array(
                            'id_kampus' => $id_kampus,
                            'id_komponen' => $jenis_tagihan[$key],
                            'id_tahun_ajar' => $id_tahun_ajar,
                            'biaya' => $nominal[$key],
                            // 'kode_prodi'=>$kode_prodi[$key],
                            'id_gelombang' => $id_gelombang,
                            'id_kelas' => $id_kelas,
                            'cicilan' => $cicilan[$key],
                            'jenis_pembayaran' => $jenis_pembayaran[$key],
                            'id_kelompok_jenjang' => $id_kelompok_jenjang,
                            'status' => 1,
                            'crdt' => date('Y-m-d H:i:s'),
                            'kode_jurusan' => $kode_jurusan,

                        );
                    }
                }


                // echo "<pre>";
                // print_r($data_tetap);
                // die;

                //echo json_encode($data_tetap);

                $data_fix2 = (array) array_merge($data_tetap, $datanya);

                if ($data_fix2) {
                    $data_fix = $data_fix2;
                } elseif ($data_tetap) {
                    $data_fix = $data_tetap;
                } else {
                    $data_fix = $datanya;
                }



                DB::table('komponen_biaya')->insert($data_fix);

                echo json_encode($this->res_insert("sukses"));
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



    function tampil_edit_buat_tarif(Request $req)
    {


        try {

            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $id_komponen = $req->id_komponen;
                $kode_jurusan = $req->kode_jurusan;
                $kelas = $req->kelas;
                $id_tahun_ajar = $req->id_tahun_ajar;
                $id_gelombang = $req->id_gelombang;
                $id_kelompok_jenjang = $req->id_kelompok_jenjang;

                $datanya = DB::table('komponen_biaya')
                    ->where('id_komponen', $id_komponen)
                    ->where('id_kampus', $id_kampus)
                    ->where('kode_jurusan', $kode_jurusan)
                    ->where('id_kelas', $kelas)
                    ->where('id_tahun_ajar', $id_tahun_ajar)
                    ->where('id_gelombang', $id_gelombang)
                    ->where('id_kelompok_jenjang', $id_kelompok_jenjang)
                    ->select('cicilan', 'biaya', 'id_komponen_biaya')
                    ->get();



                // $datanya = DB::select("select cicilan,biaya,id_komponen_biaya from komponen_biaya as k where k.id_komponen=" . $id_komponen . " and k.kode_jurusan = '" . $kode_jurusan . "' and k.id_kampus =  " . $id_kampus . "");

                // echo "<pre>";
                // print_r($datanya);
                // die;

                $total = 0;
                foreach ($datanya as $j) {


                    $total += $j->biaya;

                    $data_json[] = array(
                        'id_komponen_biaya' => $j->id_komponen_biaya,
                        'cicilan' => $j->cicilan,
                        'biaya' => $j->biaya,


                    );
                }

                $data = array(
                    'datanya' => $data_json,
                    'total' => round($total),
                );

                echo json_encode($this->show_data_object($data));
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


    function aksi_edit_buat_tarif(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                // $kode_jurusan = $req->kode_jurusan;
                $id_komponen_biaya = $req->id_komponen_biaya;
                $biaya        = $req->biaya;


                // $id_komponen_biaya    = explode(',', $id_komponen_biaya_last);
                // $biaya = explode(',', $biaya_last);


                foreach ($id_komponen_biaya as $key => $k) {

                    $data_update = array(
                        'biaya' => $biaya[$key],
                    );

                    DB::table('komponen_biaya')->where('id_komponen_biaya', $k)->update($data_update);
                }


                echo json_encode($this->res_insert("sukses"));
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


    public function list_generate_tagihan(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {
                error_reporting(0);
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;
                $kode_jurusans  = $req->kode_jurusan;
                $id_tahun_ajars = $req->id_tahun_ajar;

                $pages = $req->get('pages');
                $searchs = $req->get('search');
                $limits = $req->get('limit');
                $daris = $req->get('dari');
                $hinggas = $req->get('hingga');
                $statuss = $req->get('status');
                $kelass = $req->get('kelas');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }
                // kode mas aji
                // if ($pages) {
                //     $page = $pages - 1;
                //     $pagess = $pages;
                // } else {
                //     $page = 0;
                //     $pagess = 1;
                // }

                if ($pages) {
                    $page = $pages;
                    $pagess = $pages;
                } else {
                    $page = 0;
                    $pagess = 1;
                }

                if ($searchs) {
                    $search = $searchs;
                } else {
                    $search = '';
                }

                if ($statuss) {
                    $status = $statuss;
                } else {
                    $status = '';
                }

                // echo $status;
                // die;    

                $offset = $limit * $page;

                // kode mas aji


                //kode_jurusan = '".$kode_jurusan."'


                // if ($kode_jurusan) {
                //     $where_jurusan = "and kode_jurusan = '" . $kode_jurusan . "'";
                // } else {
                //     $where_jurusan =  "";
                // }

                // if ($id_tahun_ajar) {
                //     $where_tahun_ajar = "and id_tahun_ajar = '" . $id_tahun_ajar . "'";
                // } else {
                //     $where_tahun_ajar = "";
                // }

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


                $list_tagihan = DB::table('komponen_biaya as k')
                    ->join('m_jurusan as j', 'j.kode', '=', 'k.kode_jurusan')
                    ->join('m_komponen as kom', 'kom.id_komponen', '=', 'k.id_komponen')
                    ->select('k.id_kelas', 'k.status_generate', 'k.id_tahun_ajar', 'k.kode_jurusan', 'k.status')
                    ->where('j.nama', 'LIKE', '%' . $search . '%')


                    ->groupBy('k.id_tahun_ajar', 'k.kode_jurusan', 'k.status_generate', 'k.id_kelas', 'k.status')
                    ->where('k.id_kampus', $id_kampus)
                    ->where('kom.name_status', 'kuliah')
                    //  ->where('kom.id_event', '0')
                    ->orderBy('k.id_tahun_ajar', 'desc');

                if ($kode_jurusan) {
                    $list_tagihans = $list_tagihan->where('k.kode_jurusan', $kode_jurusan)->paginate($limit, ['*'], 'page', $pagess);
                } else {
                    $list_tagihans = $list_tagihan->paginate($limit, ['*'], 'page', $pagess);
                }

                if ($id_tahun_ajar) {
                    $list_tagihans = $list_tagihan->where('k.id_tahun_ajar', $id_tahun_ajar)->paginate($limit, ['*'], 'page', $pagess);
                } else {
                    $list_tagihans = $list_tagihan->paginate($limit, ['*'], 'page', $pagess);
                }

                if ($kelass) {
                    $list_tagihans = $list_tagihan->where('k.id_kelas', $kelass)->paginate($limit, ['*'], 'page', $pagess);
                } else {
                    $list_tagihans = $list_tagihan->paginate($limit, ['*'], 'page', $pagess);
                }

                if ($statuss == 0 || $statuss == 1) {

                    if ($statuss == 1) {
                        $list_tagihans = $list_tagihan->where('k.status_generate', '1')->paginate($limit, ['*'], 'page', $pagess);
                    } elseif ($statuss == 0 and $statuss != '') {

                        $list_tagihans = $list_tagihan->whereNull('k.status_generate')->paginate($limit, ['*'], 'page', $pagess);
                    }

                    // die;
                } else {
                    $list_tagihans = $list_tagihan->paginate($limit, ['*'], 'page', $pagess);
                    // $list_tagihans = $list_tagihan->whereNull('status')->paginate($limit, ['*'], 'page', $pagess);
                }


                // kode mas aji

                // $list_tagihan = DB::select("select kelas,status_generate,id_tahun_ajar,kode_jurusan from komponen_biaya where  id_kampus = " . $id_kampus . " 
                //       " . $where_jurusan . "
                //       " . $where_tahun_ajar . "
                //     GROUP by id_tahun_ajar,kode_jurusan,status_generate,kelas LIMIT " . $limit);



                foreach ($list_tagihans as $t) {

                    $biaya = DB::select("select sum(biaya) as biaya from komponen_biaya where id_tahun_ajar = '" . $t->id_tahun_ajar . "' and status = 1 and kode_jurusan = '" . $t->kode_jurusan . "'");

                    $jml_mhs = DB::select("select count(m.id) as total from mahasiswa as m join auth as a on a.id = m.id_auth where pmb_status = 'lulus' and a.id_kampus = '" . $id_kampus . "'  and m.jurusan1 = '" . $t->kode_jurusan . "' and m.id_tahun_ajar = '" . $t->id_tahun_ajar . "'");

                    $data_json[] = array(
                        'kode_jurusan' => $t->kode_jurusan,
                        'nama_jurusan' => $this->nama_prodi($t->kode_jurusan),
                        'tahun_ajar' => $this->tahun_ajar($t->id_tahun_ajar)->tahun_ajar,
                        'id_tahun_ajar' => $t->id_tahun_ajar,
                        'nominal' => $biaya[0]->biaya,
                        'jml_mhs' => $jml_mhs[0]->total,
                        'status' => $t->status_generate == 0 ? 0 : 1,
                        //'status2' => $t->status_generate,
                        'kelas' => $this->kelas($id_kampus, $t->id_kelas),
                        'id_kelas' => $t->id_kelas,
                    );
                }


                // $dari = $pagess;
                // $totaldata = 10;
                // $hingga = ceil($totaldata / $limit);
                // $totalhalaman = count($list_tagihans);

                $dari = $list_tagihans->currentPage();
                $hingga = $list_tagihans->lastPage();
                $totaldata = $list_tagihans->total();
                $totalhalaman = $list_tagihans->count();
                $data = $list_tagihans->items();



                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
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



    function detail_generate_tagihan(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                $id_auth            = $payload->id;
                $id_kampus          = $payload->id_kampus;
                $kode_jurusan  = $req->kode_jurusan;
                $id_tahun_ajar = $req->id_tahun_ajar;





                $list_tagihan = DB::select("select k.status_generate,k.id_tahun_ajar,k.kode_jurusan,k.id_komponen,k.jenis_pembayaran 
                    from komponen_biaya as k 
                    join m_komponen as m on k.id_komponen = m.id_komponen  
                    where  k.id_kampus = '" . $id_kampus . "' 
                     and k.kode_jurusan = '" . $kode_jurusan . "' and k.id_tahun_ajar = '" . $id_tahun_ajar . "' 
                     and m.name_status = 'kuliah' and m.id_event = 0
                     GROUP by k.id_tahun_ajar,k.kode_jurusan,k.status_generate,k.id_komponen,k.jenis_pembayaran");



                foreach ($list_tagihan as $l) {


                    $datanya = DB::select("select cicilan,biaya from komponen_biaya where  id_kampus = '" . $id_kampus . "' 
                     and kode_jurusan = '" . $kode_jurusan . "' and id_tahun_ajar = '" . $id_tahun_ajar . "' and id_komponen = '" . $l->id_komponen . "'");





                    $data_json[] = array(
                        'nama_komponen' => $this->nama_komponen($l->id_komponen),
                        'kode_jurusan' => $this->nama_prodi($kode_jurusan),
                        'jenis_pembayaran' => $l->jenis_pembayaran,
                        'listdata' => $datanya,
                    );
                }


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



    function generate_tagihan(Request $req)
    {


        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {

                ini_set('max_execution_time', 0);
                ini_set('memory_limit', -1);

                $id_auth            = $payload->id;
                $id_kampus          = $payload->id_kampus;
                $kode_jurusan_last  = $req->kode_jurusan;
                $id_tahun_ajar       = $req->id_tahun_ajar;
                $id_kelas            = $req->kelas;
                // $kode_jurusan            = $req->kode_jurusan;


                $kode_jurusan     = explode(',', $kode_jurusan_last);

                $kode = "";
                foreach ($kode_jurusan as $k) {
                    if (count($kode_jurusan) >= 2) {
                        $kode .= ",";
                    }
                    $kode .= "'" . $k . "'";
                }


                if (count($kode_jurusan) >= 2) {
                    $kode_j = substr($kode, 1);
                } else {
                    $kode_j = $kode;
                }
                //   echo $kode_j;
                // die;





                $jml_mhs = DB::select("select m.id_kelas,m.id_auth,m.id,jurusan1,id_tahun_ajar from mahasiswa as m join auth as a on a.id = m.id_auth where pmb_status = 'lulus' and a.id_kampus = " . $id_kampus . "  and m.jurusan1 in (" . $kode_j . ") and m.id_tahun_ajar = '" . $id_tahun_ajar . "'");

                // echo "select m.kelas,m.id_auth,m.id,jurusan1,id_tahun_ajar from mahasiswa as m join auth as a on a.id = m.id_auth where pmb_status = 'lulus' and a.id_kampus = " . $id_kampus . "  and m.jurusan1 in (" . $kode_j . ") and m.id_tahun_ajar = '" . $id_tahun_ajar . "'";
                // echo "<pre>";
                // print_r($jml_mhs);
                // die;


                foreach ($jml_mhs as $m) {
                    $list_tagihan = DB::select("select k.name_status,kb.id_kelas,kb.kode_jurusan,kb.id_komponen_biaya,kb.id_komponen,kb.biaya,kb.cicilan,kb.id_tahun_ajar from komponen_biaya as kb 
                        join m_komponen as k on k.id_komponen = kb.id_komponen
                        where    kb.id_kampus = " . $id_kampus . " and kb.kode_jurusan = '" . $m->jurusan1 . "' 
                        and kb.id_tahun_ajar = '" . $id_tahun_ajar . "' and kb.id_kelas = '" . $id_kelas . "' 
                        and  k.name_status = 'kuliah' and k.id_event = 0");



                    // echo "<pre>";
                    // print_r($list_tagihan);
                    // die;


                    // $detail_tagihan = \DB::table('payment_detail')
                    // ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'payment_detail.id_komponen_biaya')
                    // ->join('m_komponen', 'm_komponen.id_komponen', '=', 'komponen_biaya.id_komponen')
                    // ->where('payment_detail.id_payment',$d->id)
                    // ->select('payment_detail.*','m_komponen.nama_komponen')
                    // ->get();

                    // echo "<pre>";
                    // print_r($list_tagihan);
                    // die;

                    foreach ($list_tagihan as $l) {



                        $data_tagihan = \DB::table('tagihan_mahasiswa')
                            ->where('id_komponen_biaya', $l->id_komponen_biaya)
                            ->where('id_tahun_ajar', $l->id_tahun_ajar)
                            ->where('id_mahasiswa', $m->id)
                            ->where('cicilan', $l->cicilan)
                            ->where('kode_jurusan', $l->kode_jurusan)
                            ->where('id_kelas', $l->id_kelas)
                            ->select('id_tagihan_mahasiswa')
                            ->first();

                        $data_simpan = array(
                            'id_komponen_biaya' => $l->id_komponen_biaya,
                            'id_mahasiswa' => $m->id,
                            'nominal' => $l->biaya,
                            'cicilan' => $l->cicilan,
                            'id_tahun_ajar' => $l->id_tahun_ajar,
                            'status' => 0,
                            'id_parent' => 0,
                            'kode_jurusan' => $m->jurusan1,
                            'id_kelas' => $l->id_kelas,
                            'crdt' => date('Y-m-d H:i:s'),
                        );

                        // echo "<pre>";
                        // print_r($data_simpan);
                        // die;


                        if ($data_tagihan) {
                            //update
                            DB::table('komponen_biaya')
                                ->where('id_kampus', $id_kampus)
                                ->whereIn('kode_jurusan', $kode_jurusan)
                                ->where('id_tahun_ajar', $id_tahun_ajar)
                                ->update(['status_generate' => 1]);
                        } else {
                            //insert

                            DB::table('komponen_biaya')
                                ->where('id_kampus', $id_kampus)
                                ->whereIn('kode_jurusan', $kode_jurusan)
                                ->where('id_tahun_ajar', $id_tahun_ajar)
                                ->update(['status_generate' => 1]);

                            DB::table('tagihan_mahasiswa')->insert($data_simpan);
                        }
                    }
                }

                // echo "<pre>";
                // print_r($data_simpan);

                // $this->bulk($data_simpan);


                $generate_tagihan = DB::select("select  id_kelas,id_komponen,status_generate,id_tahun_ajar,kode_jurusan,(select count(m.id)  from mahasiswa as m join auth as a on a.id = m.id_auth where pmb_status = 'lulus' and a.id_kampus = '" . $id_kampus . "'  and m.jurusan1 = kode_jurusan and m.id_tahun_ajar = '" . $id_tahun_ajar . "') as jml_mhs from komponen_biaya where  id_kampus = '" . $id_kampus . "'
                      and kode_jurusan in (" . $kode_j . ")
                      and id_tahun_ajar = '" . $id_tahun_ajar . "' GROUP by id_komponen,status_generate,id_tahun_ajar,kode_jurusan,id_kelas");


                foreach ($generate_tagihan as $g) {
                    $biaya = DB::select("select sum(biaya) as biaya from komponen_biaya where id_tahun_ajar = '" . $g->id_tahun_ajar . "' and id_komponen = '" . $g->id_komponen . "' and status = 1 and kode_jurusan = '" . $g->kode_jurusan . "'");


                    $data_json = array(
                        'kode_jurusan' => $g->kode_jurusan,
                        'id_tahun_ajar' => $g->id_tahun_ajar,
                        'nominal' => $biaya[0]->biaya * $g->jml_mhs,
                        'jml_mhs' => $g->jml_mhs,
                        // 'id_komponen_biaya'=>$g->id_komponen_biaya,
                        'id_komponen' => $g->id_komponen,
                        'crdt' => date('Y-m-d H:i:s'),
                        'id_auth_create' => $id_auth,
                        'id_kampus' => $id_kampus,
                        'id_kelas' => $g->id_kelas,

                    );

                    $data_tagihan = \DB::table('generate_tagihan')
                        ->where('id_komponen', $g->id_komponen)
                        ->where('id_tahun_ajar', $g->id_tahun_ajar)
                        ->where('kode_jurusan', $g->kode_jurusan)
                        ->where('id_komponen', $g->id_komponen)
                        ->where('id_kampus', $id_kampus)
                        ->where('id_kelas', $g->id_kelas)
                        ->select('id_generate_tagihan')
                        ->first();

                    if ($data_tagihan) {
                    } else {
                        DB::table('generate_tagihan')->insert($data_json);
                    }
                }





                // echo "<pre>";
                // print_r($generate_tagihan);


                $data_log = array(
                    'id_kampus' => $id_kampus,
                    'crdt' => date('Y-m-d H:i:s'),
                    'message' => 'sukses',
                    'id_auth_create' => $id_auth,

                );

                DB::table('log_generate_tagihan')->insert($data_log);


                echo json_encode($this->res_insert('sukses'));
                DB::commit();
            });
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
            DB::rollback();

            // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function list_aturan_akademik(Request $req)
    {

        try {
            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;



            $pages = $req->get('pages');
            $searchs = $req->get('search');
            $limits = $req->get('limit');
            $id_tahun_ajars = $req->get('id_tahun_ajar');
            $status = $req->get('status');
            $id_komponen = $req->get('id_komponen');


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

            if ($id_tahun_ajars) {
                $id_tahun_ajar = $id_tahun_ajars;
            } else {
                $id_tahun_ajar = "";
            }


            if ($searchs) {
                $search = $searchs;
            } else {
                $search = '';
            }


            $aturan_akdemik = \DB::table('m_aturan_akademik')
                ->where('id_kampus', $id_kampus)
                ->where('nama_aturan_akademik', 'LIKE', '%' . $search . '%');
            //->paginate($limit, ['*'], 'page', $page);


            if ($id_tahun_ajars) {
                $aturan_akdemiks = $aturan_akdemik->where('id_tahun_ajar', $id_tahun_ajars)->paginate($limit, ['*'], 'page', $page);
            } else {
                $aturan_akdemiks = $aturan_akdemik->paginate($limit, ['*'], 'page', $page);
            }

            if ($status == 0 || $status == 1) {
                if ($status == 1) {
                    $aturan_akdemiks = $aturan_akdemik->where('status', $status)->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == 0 and $status != "") {
                    $aturan_akdemiks = $aturan_akdemik->where('status', $status)->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $aturan_akdemiks = $aturan_akdemik->paginate($limit, ['*'], 'page', $page);
            }

            if ($id_komponen) {
                $aturan_akdemiks = $aturan_akdemik->where('id_komponen', $id_komponen)->paginate($limit, ['*'], 'page', $page);
            } else {
                $aturan_akdemiks = $aturan_akdemik->paginate($limit, ['*'], 'page', $page);
            }





            // }


            foreach ($aturan_akdemiks as $k) {
                $data_json[] = array(
                    "id_aturan_akademik" => $k->id_aturan_akademik,
                    "nama_aturan_akademik" => $k->nama_aturan_akademik,
                    "jenis_tagihan" => $this->nama_komponen($k->id_komponen),
                    'id_komponen' => $k->id_komponen,
                    "nominal" => $k->nominal,
                    "jenis" => $k->jenis,
                    "nama_tahun_ajar" => $this->tahun_ajar($k->id_tahun_ajar)->tahun_ajar,
                    'id_tahun_ajar' => $k->id_tahun_ajar,
                    "id_peraturan" => $k->id_peraturan,
                    'nama_peraturan' => $this->peraturan($k->id_peraturan),
                    // "id_kampus"=> 51,
                    "status" => $k->status,

                    'periode_pemeriksaan' => $this->tahun_ajar($k->periode_pemerikasaan_id_tahun_ajar)->tahun_ajar,
                );
            }



            $dari = $aturan_akdemiks->currentPage();
            $hingga = $aturan_akdemiks->lastPage();
            $totaldata = $aturan_akdemiks->total();
            $totalhalaman = $aturan_akdemiks->count();
            $data = $aturan_akdemiks->items();

            echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
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



    function tambah_aturan_akademik(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $nama_aturan_akademik = $req->nama_aturan_akademik;
            $id_komponen          = $req->id_komponen;
            $id_peraturan         = $req->id_peraturan;
            $nominal              = $req->nominal;
            $jenis                = $req->jenis;
            $id_tahun_ajar        = $req->id_tahun_ajar;
            $status        = $req->status;
            $periode_pemerikasaan_id_tahun_ajar = $req->periode_pemerikasaan_id_tahun_ajar;


            $aturan_a = DB::table('m_peraturan')
                ->where('id_peraturan', $id_peraturan)
                ->first();


            $data_simpan = array(
                'nama_aturan_akademik' => $aturan_a->nama,
                'id_komponen' => $id_komponen,
                'id_peraturan' => $id_peraturan,
                'nominal' => $req->nominal,
                'jenis' => $req->jenis,
                'id_kampus' => $id_kampus,
                'status' => $status,
                'id_tahun_ajar' => $id_tahun_ajar,
                'crdt' => date('Y-m-d H:i:s'),
                'periode_pemerikasaan_id_tahun_ajar' => $periode_pemerikasaan_id_tahun_ajar,
            );

            DB::table('m_aturan_akademik')->insert($data_simpan);

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


    function edit_aturan_akademik(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $nama_aturan_akademik = $req->nama_aturan_akademik;
            $id_komponen          = $req->id_komponen;
            $id_peraturan         = $req->id_peraturan;
            $nominal              = $req->nominal;
            $jenis                = $req->jenis;
            $id_tahun_ajar        = $req->id_tahun_ajar;

            $periode_pemerikasaan_id_tahun_ajar = $req->periode_pemerikasaan_id_tahun_ajar;
            $status        = $req->status;
            $id_aturan_akademik        = $req->id_aturan_akademik;

            $aturan_a = DB::table('m_peraturan')
                ->where('id_peraturan', $id_peraturan)
                ->first();



            $data_simpan = array(
                'nama_aturan_akademik' => $aturan_a->nama,
                'id_komponen' => $id_komponen,
                'id_peraturan' => $id_peraturan,
                'nominal' => $req->nominal,
                'jenis' => $req->jenis,
                'id_kampus' => $id_kampus,
                'status' => $status,
                'id_tahun_ajar' => $id_tahun_ajar,
                'crdt' => date('Y-m-d H:i:s'),
                'periode_pemerikasaan_id_tahun_ajar' => $periode_pemerikasaan_id_tahun_ajar,
            );

            DB::table('m_aturan_akademik')->where('id_aturan_akademik', $id_aturan_akademik)->update($data_simpan);

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


    function hapus_aturan_akademik(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            // $nama_aturan_akademik = $req->nama_aturan_akademik;
            // $id_komponen          = $req->id_komponen;
            // $id_peraturan         = $req->id_peraturan;
            // $nominal              = $req->nominal;
            // $jenis                = $req->jenis;
            // $id_tahun_ajar        = $req->id_tahun_ajar;
            // $status        = $req->status;
            $id_aturan_akademik        = $req->id_aturan_akademik;


            //   $data_simpan = array(
            //     'nama_aturan_akademik'=>$nama_aturan_akademik,
            //     'id_komponen'=>$id_komponen,
            //     'id_peraturan'=>$id_peraturan,
            //     'nominal'=>$req->nominal,
            //     'jenis'=>$req->jenis,
            //     'id_kampus'=>$id_kampus,
            //     'status'=>$status,
            //     'id_tahun_ajar'=>$id_tahun_ajar,
            // );

            DB::table('m_aturan_akademik')->where('id_aturan_akademik', $id_aturan_akademik)->delete();

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


    function list_tagihan_mahasiswa(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            error_reporting(0);

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $pages = $req->get('pages');
            $searchs = $req->get('search');
            $limits = $req->get('limit');
            $id_tahun_ajars = $req->get('id_tahun_ajar');
            $kode_jurusans = $req->get('kode_jurusan');
            $id_potongans = $req->get('id_potongan');




            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            if ($pages) {
                $page = $pages - 1;
                $pagess = $pages;
            } else {
                $page = 0;
                $pagess = 1;
            }

            $offset = $limit * $page;


            if ($id_tahun_ajars != '') {
                $id_tahun_ajar = "and id_tahun_ajar = '" . $id_tahun_ajars . "'";
            } else {
                $id_tahun_ajar = "";
            }

            if ($kode_jurusans != '') {
                $kode_jurusan = "and kode_jurusan = '" . $kode_jurusans . "'";
            } else {
                $kode_jurusan = "";
            }

            if ($id_potongans != '') {
                $id_potongan = "and id_potongan = '" . $id_potongans . "'";
            } else {
                $id_potongan = "";
            }

            // echo $id_tahun_ajar;
            // die;


            if ($searchs) {
                $search = $searchs;
            } else {
                $search = '';
            }
            \DB::statement("SET SQL_MODE=''");

            // echo 'select (select nama from mahasiswa where id = tagihan_mahasiswa.id_mahasiswa) as nama_mahasiswa,id_tahun_ajar,id_mahasiswa,kode_jurusan,id_kelas,
            //                 CASE
            //                 WHEN id_potongan is not null THEN "1"
            //                 WHEN id_potongan is null THEN "0"

            //             END as potongan,
            //             CASE
            //                 WHEN tgl_akhir_dispensasi is not null THEN "1"
            //                 WHEN tgl_akhir_dispensasi is null THEN "0"

            //             END as dispensasi
            //             from `tagihan_mahasiswa`  where 1=1  '.$id_tahun_ajar.' 
            //             group by id_mahasiswa,id_tahun_ajar,kode_jurusan,id_kelas,tgl_akhir_dispensasi,id_potongan 
            //             LIMIT ' . $limit . ' OFFSET ' . $offset . '';
            // die;
            // 'LIKE', '%' . $search . '%'
            $tagihan_mhs = DB::select('select (select nama from mahasiswa where id = tagihan_mahasiswa.id_mahasiswa) as nama_mahasiswa,id_tahun_ajar,id_mahasiswa,kode_jurusan,id_kelas,
                            CASE
                            WHEN id_potongan is not null THEN "1"
                            WHEN id_potongan is null THEN "0"
                           
                        END as potongan,
                        CASE
                            WHEN tgl_akhir_dispensasi is not null THEN "1"
                            WHEN tgl_akhir_dispensasi is null THEN "0"
                           
                        END as dispensasi
                         from `tagihan_mahasiswa`  where 1=1  ' . $id_tahun_ajar . ' ' . $kode_jurusan . ' ' . $id_tahun_ajar . ' 
                        group by id_mahasiswa
                      
                        LIMIT ' . $limit . ' OFFSET ' . $offset . '');

            $count = DB::select('select (select nama from mahasiswa where id = tagihan_mahasiswa.id_mahasiswa) as nama_mahasiswa,id_tahun_ajar,id_mahasiswa,kode_jurusan,id_kelas  from `tagihan_mahasiswa` group by id_mahasiswa,id_tahun_ajar,kode_jurusan,id_kelas');





            $dari = $pagess;
            $totaldata = count($count);
            $hingga = ceil($totaldata / $limit);
            $totalhalaman = count($tagihan_mhs);


            foreach ($tagihan_mhs as $m) {
                $data_json[] = array(
                    'id_mahasiswa' => $m->id_mahasiswa,
                    'nama_mahasiswa' => $m->nama_mahasiswa,
                    'tahun_ajar' => $this->tahun_ajar($m->id_tahun_ajar)->tahun_ajar,
                    'kode_jurusan' => $this->nama_prodi($m->kode_jurusan),
                    //'kelas' => $this->kelas($id_kampus, $m->id_kelas),
                    'id_tahun_ajar' => $m->id_tahun_ajar,
                    'kode_jurusan' => $m->kode_jurusan,
                    'potongan' => $m->potongan,
                    'tgl_akhir_dispensasi' => $m->dispensasi,
                    //'id_kelas' => $m->kelas,
                    'nama_kelas' => $this->kelas($id_kampus, $m->id_kelas),
                    'id_kelas' => $m->id_kelas,


                );
            }


            echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
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



    function detail_tagihan_mahasiswa(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            error_reporting(0);

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $kelas = $req->kelas;
            $kode_jurusan = $req->kode_jurusan;
            $id_mahasiswa = $req->id_mahasiswa;

            $tagihan_mhs = DB::select("select tgl_bayar,id_tagihan_mahasiswa,(select nama from mahasiswa where id = tagihan_mahasiswa.id_mahasiswa) as nama_mahasiswa,tgl_akhir_dispensasi,potongan,id_tahun_ajar,id_mahasiswa,status,kode_jurusan,id_kelas,id_komponen_biaya,cicilan,nominal,(select id_komponen from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as id_komponen from `tagihan_mahasiswa` where id_mahasiswa = '" . $id_mahasiswa . "' and kode_jurusan = '" . $kode_jurusan . "' and id_kelas = '" . $kelas . "'");

            $jenis_tagihan = DB::select("select (select id_komponen from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as id_komponen,(select jenis_pembayaran from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as jenis_pembayaran from `tagihan_mahasiswa` where id_mahasiswa = '" . $id_mahasiswa . "' and kode_jurusan = '" . $kode_jurusan . "' and id_kelas = '" . $kelas . "'
                    GROUP by id_komponen,jenis_pembayaran");


            // echo "<pre>";
            // print_r($jenis_tagihan);
            //   die;





            foreach ($tagihan_mhs as $m) {
                $data_json[$m->id_komponen][] = array(
                    'id_tagihan_mahasiswa' => $m->id_tagihan_mahasiswa,
                    'status' => $m->status,
                    // 'id_komponen'=>$m->id_komponen,
                    // 'nama_komponen'=>$this->nama_komponen($m->id_komponen),
                    'cicilan' => $m->cicilan,
                    'nominal' => $m->nominal,
                    'waktu_pembayaran' => $m->tgl_bayar,
                    'tanggal_akhir_dispensasi' => $m->tgl_akhir_dispensasi,
                    'potongan' => $m->potongan,

                );
            }

            $data_jenis_tagihan = [];
            foreach ($jenis_tagihan as $j) {
                $data_jenis_tagihan[] = array('nama_komponen' => $this->nama_komponen($j->id_komponen), 'jenis_pembayaran' => $j->jenis_pembayaran, 'datanya' => $data_json[$j->id_komponen]);
            }

            $data = array(
                'id_mahasiswa' => $tagihan_mhs[0]->id_mahasiswa,
                'nama_mahasiswa' => $tagihan_mhs[0]->nama_mahasiswa,
                'tahun_ajar' => $this->tahun_ajar($tagihan_mhs[0]->id_tahun_ajar)->tahun_ajar,
                'kode_jurusan' => $this->nama_prodi($tagihan_mhs[0]->kode_jurusan),
                'kelas' => $this->kelas($id_kampus, $tagihan_mhs[0]->id_kelas),
                'id_tahun_ajar' => $tagihan_mhs[0]->id_tahun_ajar,
                'kode_jurusan' => $tagihan_mhs[0]->kode_jurusan,
                // 'datanya'=>array(
                //     'data_jenis_tagihan'=>$data_jenis_tagihan,
                //     'listdata'=>$data_json,
                // ),
                'listdata' => $data_jenis_tagihan,
            );


            echo json_encode($this->show_data_object($data));
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


    function list_potongan_biaya(Request $req)
    {
        try {
            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;



            $pages = $req->get('pages');
            $searchs = $req->get('search');
            $limits = $req->get('limit');
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




            if ($searchs) {
                $search = $searchs;
            } else {
                $search = '';
            }




            $potongan_biaya = \DB::table('potongan_biaya')
                ->where('id_kampus', $id_kampus)
                ->where('nama_potongan', 'LIKE', '%' . $search . '%')
                ->paginate($limit, ['*'], 'page', $page);



            foreach ($potongan_biaya as $k) {
                $data_json[] = array(
                    "id_potongan_biaya" => $k->id_potongan_biaya,
                    "nama_potongan" => $k->nama_potongan,
                    "peserta" => $k->peserta,
                    "nominal" => $k->nominal,
                    "realisasi" => $k->realisasi,
                    "sumber" => $k->sumber,
                    "tgl_mulai" => $k->periode_awal,
                    "anggaran" => $k->anggaran,
                    "tgl_selesai" => $k->periode_akhir,
                    "status" => $k->status,
                );
            }



            $dari = $potongan_biaya->currentPage();
            $hingga = $potongan_biaya->lastPage();
            $totaldata = $potongan_biaya->total();
            $totalhalaman = $potongan_biaya->count();
            $data = $potongan_biaya->items();

            echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
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

    function tambah_potongan_biaya(Request $req)
    {
        try {
            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;




            $data_simpan = array(
                //"id_potongan_biaya"=> $req->id_potongan_biaya,
                "nama_potongan" => $req->nama_potongan,
                "peserta" => $req->peserta,
                "nominal" => $req->nominal,
                "realisasi" => $req->realisasi,
                "sumber" => $req->sumber,
                "anggaran" => $req->anggaran,
                "periode_awal" => $req->periode_awal,
                "periode_akhir" => $req->periode_akhir,
                "id_kampus" => $id_kampus,
                "status" => 1,
            );


            DB::table('potongan_biaya')->insert($data_simpan);

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



    function edit_potongan_biaya(Request $req)
    {
        try {
            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;




            $data_simpan = array(
                //"id_potongan_biaya"=> $req->id_potongan_biaya,
                "nama_potongan" => $req->nama_potongan,
                "peserta" => $req->peserta,
                "nominal" => $req->nominal,
                "realisasi" => $req->realisasi,
                "sumber" => $req->sumber,
                "anggaran" => $req->anggaran,
                "periode_awal" => $req->periode_awal,
                "periode_akhir" => $req->periode_akhir,
                "id_kampus" => $id_kampus,
                "status" => 1,
            );


            DB::table('potongan_biaya')->where('id_potongan_biaya', $req->id_potongan_biaya)->update($data_simpan);

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


    function hapus_potongan_biaya(Request $req)
    {
        try {
            error_reporting(0);
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;




            // $data_simpan = array(
            //     //"id_potongan_biaya"=> $req->id_potongan_biaya,
            //     "nama_potongan"=> $req->nama_potongan,
            //     "peserta"=> $req->peserta,
            //     "nominal"=> $req->nominal,
            //     "realisasi"=> $req->realisasi,
            //     "sumber"=> $req->sumber,
            //     "tgl_mulai"=> $req->tgl_mulai,
            //     "anggaran_mhs"=> $req->anggaran_mhs,
            //     "tgl_selesai"=> $req->tgl_selesai,
            //     "status"=>$req->status,
            // );




            DB::table('potongan_biaya')->where('id_potongan_biaya', $req->id_potongan_biaya)->delete();

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

    function detail_tagihan_mahasiswa_dispensasi(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            error_reporting(0);

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $id_kelas = $req->kelas;
            $kode_jurusan = $req->kode_jurusan;
            $id_mahasiswa = $req->id_mahasiswa;

            $tagihan_mhs = DB::select("select tgl_akhir_dispensasi,tgl_bayar,id_tagihan_mahasiswa,(select nama from mahasiswa where id = tagihan_mahasiswa.id_mahasiswa) as nama_mahasiswa,id_tahun_ajar,id_mahasiswa,status,kode_jurusan,id_kelas,id_komponen_biaya,cicilan,nominal,(select id_komponen from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as id_komponen from `tagihan_mahasiswa` where id_mahasiswa = '" . $id_mahasiswa . "' and kode_jurusan = '" . $kode_jurusan . "' and id_kelas = '" . $id_kelas . "'");

            $jenis_tagihan = DB::select("select (select id_komponen from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as id_komponen,(select jenis_pembayaran from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as jenis_pembayaran from `tagihan_mahasiswa` where id_mahasiswa = '" . $id_mahasiswa . "' and kode_jurusan = '" . $kode_jurusan . "' and id_kelas = '" . $id_kelas . "'
                    GROUP by id_komponen,jenis_pembayaran");



            // echo "<pre>";
            // print_r($jenis_tagihan);
            //   die;





            foreach ($tagihan_mhs as $m) {
                $data_json[$m->id_komponen][] = array(
                    'id_tagihan_mahasiswa' => $m->id_tagihan_mahasiswa,
                    'status' => $m->status,
                    // 'id_komponen'=>$m->id_komponen,
                    // 'nama_komponen'=>$this->nama_komponen($m->id_komponen),
                    'cicilan' => $m->cicilan,
                    'nominal' => $m->nominal,
                    'tenggang_waktu' => $m->tgl_akhir_dispensasi

                );
            }

            $data_jenis_tagihan = [];
            foreach ($jenis_tagihan as $j) {
                $data_jenis_tagihan[] = array('nama_komponen' => $this->nama_komponen($j->id_komponen), 'jenis_pembayaran' => $j->jenis_pembayaran, 'datanya' => $data_json[$j->id_komponen]);
            }

            $data = array(
                'id_mahasiswa' => $tagihan_mhs[0]->id_mahasiswa,
                'nama_mahasiswa' => $tagihan_mhs[0]->nama_mahasiswa,
                'tahun_ajar' => $this->tahun_ajar($tagihan_mhs[0]->id_tahun_ajar)->tahun_ajar,
                'kode_jurusan' => $this->nama_prodi($tagihan_mhs[0]->kode_jurusan),
                'id_kelas' => $tagihan_mhs[0]->id_kelas,
                'id_tahun_ajar' => $tagihan_mhs[0]->id_tahun_ajar,
                'kode_jurusan' => $tagihan_mhs[0]->kode_jurusan,
                // 'datanya'=>array(
                //     'data_jenis_tagihan'=>$data_jenis_tagihan,
                //     'listdata'=>$data_json,
                // ),
                'listdata' => $data_jenis_tagihan,
            );


            echo json_encode($this->show_data_object($data));
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

    function tambah_mahasiswa_dispensasi(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            error_reporting(0);

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $id_tagihan_mahasiswa = $req->id_tagihan_mahasiswa;
            $data_simpan = array(
                'tgl_akhir_dispensasi' => $req->tgl_akhir_dispensasi
            );
            DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $id_tagihan_mahasiswa)->update($data_simpan);
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
    function hapus_mahasiswa_dispensasi(Request $req)
    {

        try {
            $headers = apache_request_headers(); //get header
            $req->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');

            error_reporting(0);

            $id_auth        = $payload->id;
            $id_kampus      = $payload->id_kampus;
            $id_tagihan_mahasiswa = $req->id_tagihan_mahasiswa;
            $data_simpan = array(
                'tgl_akhir_dispensasi' => null
            );
            DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $id_tagihan_mahasiswa)->update($data_simpan);
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

    function potongan_kampus(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            //die;
            $potongan_kampus = DB::table('potongan_biaya')
                ->where('id_kampus', $id_kampus)
                ->where('status', 1)
                ->where('periode_awal', '<=', date('Y-m-d'))
                ->where('periode_akhir', '>=', date('Y-m-d'))
                ->get();


            foreach ($potongan_kampus as $p) {
                $data_json[] = array(
                    "id_potongan_biaya" => $p->id_potongan_biaya,
                    "nama_potongan" =>  $p->nama_potongan,
                    "nominal" =>  $p->nominal,
                    "anggaran" =>  $p->anggaran,
                    "sumber" =>  $p->sumber,
                    "realisasi" =>  $p->realisasi,
                    "peserta" =>  $p->peserta,
                    "id_kampus" =>  $p->id_kampus,
                    "status" =>  $p->status,
                    "jenis_potongan" =>  $p->jenis_potongan,
                    "periode_awal" =>  $p->periode_awal,
                    "periode_akhir" =>  $p->periode_akhir,
                    'sisa' => $p->nominal - $p->realisasi,

                );
            }


            echo json_encode($this->show_data_object($data_json));
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
    function pembiayaan_potongan(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            //die;
            $id_komponens = DB::table('komponen_biaya as kb')
                ->join('m_komponen as m', 'm.id_komponen', '=', 'kb.id_komponen')
                ->select('kb.id_komponen')
                ->where('kb.id_kampus', $id_kampus)
                ->where('kb.status', 1)
                ->where('m.name_status', 'kuliah')
                ->where('id_event', '0')
                ->where('kb.id_tahun_ajar', $request->id_tahun_ajar)
                ->where('kb.kode_jurusan', $request->kode_jurusan)
                ->groupBy('kb.id_komponen')->get();


            foreach ($id_komponens as $id_komponen) {
                $data_json[] = array(
                    'id_komponen' => $id_komponen->id_komponen,
                    'nama_komponen' => $this->nama_komponen($id_komponen->id_komponen)
                );
            }
            echo json_encode($this->show_data_object($data_json));
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
    function list_potongan_kampus(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            //die;
            $list_potongan_kampus = DB::table('tagihan_mahasiswa')
                ->Leftjoin('potongan_biaya as p', 'p.id_potongan_biaya', '=', 'tagihan_mahasiswa.id_potongan')
                ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'tagihan_mahasiswa.id_komponen_biaya')
                ->where('tagihan_mahasiswa.id_mahasiswa', $request->id_mahasiswa)
                ->where('komponen_biaya.id_komponen', $request->id_komponen)
                ->select('komponen_biaya.id_komponen', 'tagihan_mahasiswa.nominal', 'tagihan_mahasiswa.id_potongan', 'tagihan_mahasiswa.potongan', 'tagihan_mahasiswa.id_tagihan_mahasiswa', 'tagihan_mahasiswa.cicilan', 'tagihan_mahasiswa.status', 'p.nama_potongan')
                ->get();

            foreach ($list_potongan_kampus as $p) {

                if ($p->id_potongan > 0) {
                    $status = 1;
                } else {
                    $status = 0;
                }
                $data_json[] = array(
                    "id_komponen" => $p->id_komponen,
                    "nominal" => $p->nominal,
                    "id_potongan" => $p->id_potongan,
                    "potongan" => $p->potongan,
                    'nama_potongan' => $p->nama_potongan,
                    "id_tagihan_mahasiswa" => $p->id_tagihan_mahasiswa,
                    "cicilan" =>  $p->cicilan,
                    "status_byr" =>  $p->status,
                    'status' => $status,
                );
            }

            echo json_encode($this->show_data_object($data_json));
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
    function simpan_pengajuan_potongan(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_mahasiswa = DB::table('mahasiswa')->where('id_auth', $id_auth)->select('id')->first()->id;
            $id_potongan = $request->id_potongan;
            $id_tagihan_mahasiswa_last = $request->id_tagihan_mahasiswa;
            $potongan_last = $request->potongan;

            $id_tagihan_mahasiswa = is_array($id_tagihan_mahasiswa_last) ? $id_tagihan_mahasiswa_last : explode(',', $id_tagihan_mahasiswa_last);
            $potongan = is_array($potongan_last) ? $potongan_last : explode(',', $potongan_last);
            $total_realisasi = 0;
            $counts = DB::table('tagihan_mahasiswa')
                ->join('potongan_biaya', 'potongan_biaya.id_potongan_biaya', '=', 'tagihan_mahasiswa.id_potongan')
                ->select('tagihan_mahasiswa.id_mahasiswa')
                ->where('tagihan_mahasiswa.id_potongan', $id_potongan)
                ->where('tagihan_mahasiswa.id_mahasiswa', $id_mahasiswa)
                ->groupBy('tagihan_mahasiswa.id_mahasiswa')
                ->get();

            $jumlah_peserta_potongan_biaya = (int) DB::table('potongan_biaya')
                ->select('peserta')
                ->where('id_potongan_biaya', $id_potongan)
                ->where('status', 1)
                ->get()[0]->peserta;

            if (count($counts) <= $jumlah_peserta_potongan_biaya) {


                $potongan_r = DB::table('potongan_biaya')->select('realisasi', 'anggaran', 'nominal')->where('id_potongan_biaya', $id_potongan)->first();
                $jumlah_potongan_sebelumnya = 0;
                foreach ($id_tagihan_mahasiswa as $key => $isi) {
                    $jumlah = DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $isi)->sum('potongan');
                    $jumlah_potongan_sebelumnya += $jumlah;
                    $total_realisasi += $potongan[$key];

                    if ($potongan[$key] == '0') {
                        $a = DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $isi)->select('potongan')->first();
                        $b = DB::table('potongan_biaya')->where('id_potongan_biaya', $id_potongan)->select('nominal')->first();
                        $hsl = $b->nominal - $a->potongan;
                        DB::table('potongan_biaya')->where('id_potongan_biaya', $id_potongan)->update(
                            [
                                'nominal' => $hsl,
                                'realisasi' => $hsl
                            ]
                        );
                    }
                }


                // total realisasi dari potongan yang diinput sekarang


                $hasil_dari_total = $potongan_r->nominal + $total_realisasi;


                if ($hasil_dari_total <= $potongan_r->anggaran) {
                    $hasilnya = $potongan_r->realisasi - $jumlah_potongan_sebelumnya;

                    DB::table('potongan_biaya')->where('id_potongan_biaya', $id_potongan)->update([
                        'realisasi' => $hasilnya,
                        'nominal' => $hasilnya
                    ]);
                    foreach ($id_tagihan_mahasiswa as $key => $isi) {
                        $id_potongan_proses = $potongan[$key] > 0 ? $id_potongan : '0';
                        $data_simpan = array(
                            'id_potongan' => $id_potongan_proses,
                            'potongan' => $potongan[$key],
                        );
                        DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $isi)->update($data_simpan);
                    }

                    $jumlah_sebelumnya = DB::table('potongan_biaya')->select('realisasi')->where('id_potongan_biaya', $id_potongan)->get()[0]->realisasi;
                    $now = $jumlah_sebelumnya += $total_realisasi;
                    $realisasi = array(
                        'realisasi' => $now,
                        'nominal' => $now,
                    );

                    DB::table('potongan_biaya')->where('id_potongan_biaya', $id_potongan)->update($realisasi);
                    $status = 1;
                    $message = "success";
                    $pesannya = [
                        'status' => $status,
                        'message' => $message
                    ];
                    echo json_encode($this->show_data_object($pesannya));
                } else {
                    $status = 99;
                    $message = "jumlah realisasi melebihi anggaran";
                    $pesannya = [
                        'status' => $status,
                        'message' => $message
                    ];
                    echo json_encode($this->show_data_object($pesannya));
                }
            } else {
                $status = 98;
                $message = "melebihi " . $jumlah_peserta_potongan_biaya . " peserta";
                $pesannya = [
                    'status' => $status,
                    'message' => $message
                ];
                echo json_encode($this->show_data_object($pesannya));
            }
            // echo json_encode($this->show_data_object($list_potongan_kampus));
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

    function update_tagihan_mahasiswa(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_mahasiswa = DB::table('mahasiswa')->select('id')->where('id_auth', $id_auth)->first();

            $id_kampus = $payload->id_kampus;
            //die;
            $id_tagihan_mahasiswa = $request->id_tagihan_mahasiswa;
            $update = DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $id_tagihan_mahasiswa)->update([
                "potongan" => 0,
                "id_potongan" => null
            ]);

            if ($update) {
                $status = 1;
                $message = "success";
                $pesannya = [
                    'status' => $status,
                    'message' => $message
                ];
                echo json_encode($this->show_data_object($pesannya));
            } else {
                $status = 99;
                $message = "gagal diupdate";
                $pesannya = [
                    'status' => $status,
                    'message' => $message
                ];
                echo json_encode($this->show_data_object($pesannya));
            }


            // echo json_encode($this->show_data_object($list_potongan_kampus));
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

    function list_penerima_potongan(Request $req)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth   = $payload->id;
            $searchs = $req->get('search');
            $limits = $req->get('limit');
            $pages = $req->get('pages');
            $kode_pokoks = $req->get('kode_pokok');
            $kode_jurusans = $req->get('kode_jurusan');
            $nama_potongans = $req->get('id_potongan_biaya');
            $sumber_potongans = $req->get('sumber_potongan');

            if ($searchs) {
                $search = $searchs;
            } else {
                $search = '';
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

            if ($nama_potongans) {
                $nama_potongan = $nama_potongans;
            } else {
                $nama_potongan = '';
            }

            if ($kode_pokoks) {
                $kode_pokok = $kode_pokoks;
            } else {
                $kode_pokok = '';
            }
            if ($sumber_potongans) {
                $sumber_potongan = $sumber_potongans;
            } else {
                $sumber_potongan = '';
            }

            // if ($sumber_potongans){
            //     $sumber_
            // }


            $lists = DB::table('tagihan_mahasiswa')
                ->join('mahasiswa', 'mahasiswa.id', '=', 'tagihan_mahasiswa.id_mahasiswa')
                ->join('potongan_biaya', 'potongan_biaya.id_potongan_biaya', '=', 'tagihan_mahasiswa.id_potongan')
                ->where('mahasiswa.nama', 'LIKE', '%' . $search . '%')
                ->select('tagihan_mahasiswa.id_mahasiswa', 'tagihan_mahasiswa.kode_jurusan', 'potongan_biaya.nama_potongan', 'potongan_biaya.sumber', 'mahasiswa.nama', 'tagihan_mahasiswa.kode_jurusan')
                ->groupBy('tagihan_mahasiswa.id_mahasiswa', 'tagihan_mahasiswa.kode_jurusan', 'potongan_biaya.nama_potongan', 'potongan_biaya.sumber', 'mahasiswa.nama', 'tagihan_mahasiswa.kode_jurusan');


            // 'LIKE', '%' . $nama_potongan . '%'

            if ($kode_jurusan) {
                $lists = $lists->where('tagihan_mahasiswa.kode_jurusan', $kode_jurusan)->paginate($limit, ['*'], 'page', $page);
            } else if ($nama_potongan) {
                $lists = $lists->where('potongan_biaya.id_potongan_biaya', $nama_potongan)->paginate($limit, ['*'], 'page', $page);
            } else if ($sumber_potongan) {
                $lists = $lists->where('potongan_biaya.sumber', $sumber_potongan)->paginate($limit, ['*'], 'page', $page);
            } else {
                $lists = $lists->paginate($limit, ['*'], 'page', $page);
            }
            foreach ($lists as $list) {
                $datanya[] = array(
                    "nama_lengkap" => $list->nama,
                    "no_pokok" => 0,
                    "kode_jurusan" => $list->kode_jurusan,
                    "nama_jurusan" => $this->nama_prodi($list->kode_jurusan),
                    "nama_potongan" => $list->nama_potongan,
                    "sumber_potongan" => $list->sumber
                );
            }

            $dari = $lists->currentPage();
            $hingga = $lists->lastPage();
            $totaldata = $lists->total();
            $totalhalaman = count($datanya);
            $data = $lists->items();

            echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $datanya));




            // echo json_encode($this->show_data_object($list_potongan_kampus));
        } catch (Exception $e) {
            return response()->json(["data" => $req->all(), "log" => $e->getMessage()]);
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
