<?php

namespace App\Http\Controllers;

use App\Models\MasterKampus;
use App\Models\BerkasMahasiswa;


use App\Models\Mahasiswa;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\utility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class PmbAdminController extends Controller
{
    use utility;
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }




    public function dashboard()
    {
        try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use ($payload) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                $gelombang = DB::table('pmb_gelombang')
                    ->where('id_kampus', $id_kampus)
                    ->get();








                foreach ($gelombang as $g) {
                    $peserta = DB::table('mahasiswa')
                        ->where('id_gelombang', $g->id_gelombang)
                        ->count();

                    $peserta_total = DB::table('mahasiswa')
                        // ->where('id_gelombang',$g->id_gelombang)
                        ->count();



                    $data2[] = array(
                        "id_gelombang" => $g->id_gelombang,
                        "nama_gelombang" => $g->nama_gelombang,
                        "tgl_mulai" => $g->tgl_mulai,
                        "tgl_selesai" => $g->tgl_selesai,
                        "status" => $g->status,
                        "id_kampus" => $g->id_kampus,
                        "tahun_ajar" => $g->tahun_ajar,
                        "ujian_seleksi" => $g->ujian_seleksi,
                        'peserta' => $peserta
                    );
                }








                echo json_encode($this->show_data_object($data2));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function m_kampus_prodi(Request $request)
    {
        try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_kampus = $payload->id_kampus;
            $search = $request->get('search');
            $pages = $request->get('pages');
            $statuss = $request->get('status');

            $limits = $request->get('limit');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 5;
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }



            $kampus = DB::table('kampus')
                ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
                ->where('kampus.id', $id_kampus)
                ->select('kampus.prodi', 'kampus.id')
                ->first();

            $prodi =  json_decode($kampus->prodi);



            return response()->json($prodi);
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function m_kampus_visi_misi(Request $request)
    {
        try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            $id_kampus = $payload->id_kampus;
            $search = $request->get('search');
            $pages = $request->get('pages');
            $statuss = $request->get('status');

            $limits = $request->get('limit');

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 5;
            }

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }



            $kampus = DB::table('kampus')
                ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
                ->where('kampus.id', $id_kampus)
                ->select('kampus.keterangan', 'kampus.id')
                ->first();

            $prodi =  json_decode($kampus->keterangan);
            $data2 = array(
                'sejarah' => $prodi->sejarah,
                'visi' => $prodi->visi,
                'misi' => $prodi->misi,
                'slogan' => $prodi->slogan,
                'youtube' => $prodi->youtube
            );


            return response()->json($data2);
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function master_kampus_prodi(Request $request)
    {
        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', $headers['Authorization']);
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                error_reporting(0);

                //$id_kampus = 51;
                $search = $request->get('search');
                $pages = $request->get('pages');
                $statuss = $request->get('status');

                $limits = $request->get('limit');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 5;
                }

                if (isset($statuss)) {
                    $status = $statuss == '1' ? '1' : '0';
                } else {
                    $status = 'null';
                }

                if ($pages) {
                    $page = $pages;
                } else {
                    $page = 1;
                }




                $prodi = DB::table('m_program_studi as p')
                    ->join('m_jurusan as m', 'm.kode', '=', 'p.kode')
                    ->where('p.id_kampus', $id_kampus)
                    ->where('p.nama_prodi', 'LIKE', '%' . $search . '%')
                    ->select('*')
                    ->paginate($limit, ['p.*,m.jenjang'], 'page', $page);



                $dari = $prodi->currentPage();
                $hingga = $prodi->lastPage();
                $totaldata = $prodi->total();
                $totalhalaman = $prodi->count();
                $data_prodi = $prodi->items();

                foreach ($data_prodi as $k) {

                    // $jrs = DB::table('m_jurusan')
                    //     ->where('kode', $k->kode)
                    //     ->select('*')
                    //     ->first();


                    $data_json[] = array(
                        'id' => $k->id_program_studi,
                        'kode' => $k->kode,
                        'nama' => $k->nama_prodi,
                        'jenjang' => $k->jenjang,
                        'nama_kelompok_jenjang' => $this->kelompok_jenjang($k->id_kelompok_jenjang),
                        'id_kelompok_jenjang' => $k->id_kelompok_jenjang,
                        'kelas' => $k->kelas,
                        // 'gelar' => $k->gelar,
                        // 'karir' => $k->karir,
                        // 'komp' => $k->komp,
                        'youtube' => $k->youtube,
                        'akreditasi' => $k->akre,
                        'kuota' => $k->kouta,
                        'status' => $k->status,

                    );
                }



                // echo "<pre>";
                // print_r($prodi);


                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data_json));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }



    // function master_kampus_prodi_tambah(Request $request)
    // {
    //     try {

    //         $payload            = JWTAuth::parseToken()->getPayload()->get('data');
    //         DB::transaction(function () use ($payload, $request) {
    //             $id_auth        = $payload->id;
    //             $id_kampus      = $payload->id_kampus;

    //             $kode = $request->kode;
    //             $akreditasi = $request->akreditasi;
    //             $kelas = $request->kelas;
    //             $id_kelompok_jenjang = $request->id_kelompok_jenjang;

    //             $data_prod = DB::table('m_jurusan')
    //                 ->where('kode', $kode)
    //                 ->select('id', 'kode', 'nama', 'jenjang', 'gelar', 'karir', 'komp', 'youtube')
    //                 ->first();

    //             $prodi_tambah[] = (object)[
    //                 'akre' => $akreditasi,
    //                 'kode' => $kode,
    //                 'nama' => $data_prod->nama,
    //                 'kelas' => $kelas,
    //                 'id_kelompok_jenjang' => $id_kelompok_jenjang,
    //             ];


    //             $kampus = DB::table('kampus')
    //                 ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
    //                 ->where('kampus.id', $id_kampus)
    //                 ->select('kampus.prodi', 'kampus.id')
    //                 ->first();

    //             $prodi = json_decode($kampus->prodi);

    //             $prodi_tambah_simpan = array_merge($prodi, $prodi_tambah);

    //             echo "lagi di benerin";
    //             die;
    //             // dd($prodi_tambah_simpan);



    //             DB::table('kampus')->where('id_kampus', $id_kampus)
    //                 ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
    //                 ->where('kampus.id', $id_kampus)
    //                 ->update(['prodi' => json_encode($prodi_tambah_simpan)]);


    //             ///

    //             $jurusan = $request->jurusan;

    //             $komp = $request->komp;
    //             $karir = $request->karir;
    //             $kode = $request->kode;



    //             $data = MasterKampus::where('id', $id_kampus)->firstOrFail();
    //             $propektus = (array)json_decode($data->attr_propektus);





    //             foreach ($propektus as $key1 => $p) {


    //                 if ($p->kode == $kode) {
    //                     //update
    //                     $update[$p->kode] = $key1;
    //                 } else {
    //                     //tambah
    //                     // echo "tambah";
    //                 }
    //             }


    //             error_reporting(0);

    //             if ($update) {
    //                 //update



    //                 $prospek_edit[$update[$kode]] = array(
    //                     'kode' => $kode,
    //                     'komp' => $komp,
    //                     'karir' => $karir,
    //                 );



    //                 $edit_simpan = array_replace_recursive($propektus, $prospek_edit);

    //                 DB::table('kampus')->where('id', $id_kampus)->update(['attr_propektus' => json_encode($edit_simpan)]);
    //             } else {
    //                 // tambah 
    //                 // echo "tambah";

    //                 $prospek_tambah[] = array(
    //                     'kode' => $kode,
    //                     'komp' => $komp,
    //                     'karir' => $karir,
    //                 );

    //                 $prospek_simpan = array_merge($propektus, $prospek_tambah);


    //                 DB::table('kampus')->where('id', $id_kampus)->update(['attr_propektus' => json_encode($prospek_simpan)]);
    //             }
    //         });

    //         return response()->json($this->res_insert("sukses"));
    //     } catch (Exception $e) {
    //         return response()->json(["log" => $e->getMessage()]);
    //         //return response()->json($this->res_insert("fatal"));
    //     } catch (TokenExpiredException $e) {
    //         return response()->json($this->res_insert("token_expired"));
    //     } catch (TokenInvalidException $e) {
    //         return response()->json($this->res_insert("token_invalid"));
    //     } catch (JWTException $e) {
    //         return response()->json($this->res_insert("token_absent"));
    //     }
    // }


    // function master_kampus_prodi_edit(Request $request)
    // {
    //     try {

    //         $payload            = JWTAuth::parseToken()->getPayload()->get('data');


    //         DB::transaction(function () use ($payload, $request) {
    //             $id_auth        = $payload->id;
    //             $id_kampus      = $payload->id_kampus;

    //             $kode = $request->kode;
    //             $akreditasi = $request->akreditasi;
    //             $kelas = $request->kelas;
    //             $id_kelompok_jenjang = $request->id_kelompok_jenjang;

    //             $kode_edit = $request->kode_edit;

    //             $data_prod = DB::table('m_jurusan')
    //                 ->where('kode', $kode)
    //                 ->select('id', 'kode', 'nama', 'jenjang', 'gelar', 'karir', 'komp', 'youtube')
    //                 ->first();





    //             $kampus = DB::table('kampus')
    //                 ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
    //                 ->where('kampus.id', $id_kampus)
    //                 ->select('kampus.prodi', 'kampus.id')
    //                 ->first();

    //             $prodi = (array)json_decode($kampus->prodi);


    //             foreach ($prodi as $key => $p) {
    //                 $search[$p->kode] = $key;
    //             }

    //             $prodi_tambah[$search[$kode_edit]] = array(
    //                 'akre' => $akreditasi,
    //                 'kode' => $kode,
    //                 'nama' => $data_prod->nama,
    //                 'kelas' => $kelas,
    //                 'id_kelompok_jenjang' => $id_kelompok_jenjang,
    //             );



    //             // echo "<pre>";
    //             // print_r($search[$kode_edit]);

    //             //  echo "<pre>";
    //             // print_r($prodi);

    //             //  echo "<pre>";
    //             // print_r($prodi_tambah);

    //             $edit_simpan = array_replace_recursive($prodi, $prodi_tambah);


    //             DB::table('kampus')->where('id_kampus', $id_kampus)
    //                 ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
    //                 ->where('kampus.id', $id_kampus)
    //                 ->update(['prodi' => json_encode($edit_simpan)]);



    //             /// Propektus

    //             $jurusan = $request->jurusan;

    //             $komp = $request->komp;
    //             $karir = $request->karir;
    //             // $kode = $request->kode;



    //             $data = MasterKampus::where('id', $id_kampus)->firstOrFail();
    //             $propektus = (array)json_decode($data->attr_propektus);





    //             foreach ($propektus as $key1 => $p) {


    //                 if ($p->kode == $kode_edit) {
    //                     //update
    //                     $update[$p->kode] = $key1;
    //                 } else {
    //                     //tambah
    //                     // echo "tambah";
    //                 }
    //             }


    //             error_reporting(0);

    //             if ($update) {
    //                 //update



    //                 $prospek_edit[$update[$kode_edit]] = array(
    //                     'kode' => $kode_edit,
    //                     'komp' => $komp,
    //                     'karir' => $karir,
    //                 );



    //                 $edit_simpan = array_replace_recursive($propektus, $prospek_edit);

    //                 DB::table('kampus')->where('id', $id_kampus)->update(['attr_propektus' => json_encode($edit_simpan)]);
    //             } else {
    //                 // tambah 
    //                 // echo "tambah";

    //                 $prospek_tambah[] = array(
    //                     'kode' => $kode_edit,
    //                     'komp' => $komp,
    //                     'karir' => $karir,
    //                 );

    //                 $prospek_simpan = array_merge($propektus, $prospek_tambah);


    //                 DB::table('kampus')->where('id', $id_kampus)->update(['attr_propektus' => json_encode($prospek_simpan)]);
    //             }
    //         });

    //         return response()->json($this->res_insert("sukses"));
    //     } catch (Exception $e) {
    //         return response()->json(["log" => $e->getMessage()]);
    //         //return response()->json($this->res_insert("fatal"));
    //     } catch (TokenExpiredException $e) {
    //         return response()->json($this->res_insert("token_expired"));
    //     } catch (TokenInvalidException $e) {
    //         return response()->json($this->res_insert("token_invalid"));
    //     } catch (JWTException $e) {
    //         return response()->json($this->res_insert("token_absent"));
    //     }
    // }


    // function master_kampus_prodi_hapus(Request $request)
    // {
    //     try {

    //         $payload            = JWTAuth::parseToken()->getPayload()->get('data');


    //         DB::transaction(function () use ($payload, $request) {
    //             $id_auth        = $payload->id;
    //             $id_kampus      = $payload->id_kampus;

    //             //$kode = $request->kode;
    //             // $akreditasi = $request->akreditasi;

    //             $kode_edit = $request->kode_edit;
    //             $kelas = $request->kelas;
    //             $id_kelompok_jenjang = $request->id_kelompok_jenjang;


    //             $kampus = DB::table('kampus')
    //                 ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
    //                 ->where('kampus.id', $id_kampus)
    //                 ->select('kampus.prodi', 'kampus.id')
    //                 ->first();

    //             $prodi = (array)json_decode($kampus->prodi);


    //             //    foreach($prodi as $key => $p){

    //             //        // $hapus = DB::select("UPDATE `kampus` set `prodi`  =  json_remove(`prodi`, '$[0]') where `id` = 51");


    //             //    }

    //             //   echo $hapus =  DB::update("UPDATE `kampus` set `prodi`  =  json_remove(`prodi`, '$[0]') where `id` = 51");
    //             //           //echo json_encode('sukses');


    //             // die;

    //             $data_prod = DB::table('m_jurusan')
    //                 ->where('kode', $kode_edit)
    //                 ->select('id', 'kode', 'nama', 'jenjang', 'gelar', 'karir', 'komp', 'youtube')
    //                 ->first();






    //             // echo "<pre>";
    //             //     print_r($prodi);

    //             //     die;

    //             foreach ($prodi as $key => $p) {
    //                 $search[$p->kode][$p->kelas][$p->id_kelompok_jenjang] = $key;
    //                 $data_prodii[$p->kode][$p->kelas][$p->id_kelompok_jenjang] = $p;
    //             }



    //             $prodi_tambah[$search[$kode_edit][$kelas][$id_kelompok_jenjang]] = array(
    //                 'akre' => '',
    //                 'kode' => $data_prod->kode,
    //                 'nama' => $data_prod->nama,
    //                 'kelas' => $data_prodii[$kode_edit][$kelas][$id_kelompok_jenjang]->kelas,
    //                 'id_kelompok_jenjang' => $data_prodii[$kode_edit][$kelas][$id_kelompok_jenjang]->id_kelompok_jenjang,
    //             );



    //             unset($prodi[$search[$kode_edit][$kelas][$id_kelompok_jenjang]]);




    //             //  echo "<pre>";
    //             // print_r($prodi_tambah);




    //             // die;

    //             //  $edit_simpan = array_replace_recursive($prodi,$prodi_tambah);

    //             foreach ($prodi as $p) {
    //                 $data_simpan_h[] = array(
    //                     'akre' => $p->akre,
    //                     'kode' => $p->kode,
    //                     'nama' => $p->nama,
    //                     'kelas' => $p->kelas,
    //                     'id_kelompok_jenjang' => $p->id_kelompok_jenjang,
    //                 );
    //             }
    //             // echo "<pre>";
    //             // print_r($data_simpan_h);

    //             // die;


    //             DB::table('kampus')->where('id_kampus', $id_kampus)
    //                 ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
    //                 ->where('kampus.id', $id_kampus)
    //                 ->update(['prodi' => json_encode($data_simpan_h)]);
    //         });

    //         return response()->json($this->res_insert("sukses"));
    //     } catch (Exception $e) {
    //         return response()->json(["log" => $e->getMessage()]);
    //         //return response()->json($this->res_insert("fatal"));
    //     } catch (TokenExpiredException $e) {
    //         return response()->json($this->res_insert("token_expired"));
    //     } catch (TokenInvalidException $e) {
    //         return response()->json($this->res_insert("token_invalid"));
    //     } catch (JWTException $e) {
    //         return response()->json($this->res_insert("token_absent"));
    //     }
    // }

    function master_kampus_visi_misi(Request $request)
    {
        try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                // $visi = $request->visi;
                // $misi = $request->misi;
                // $sejarah = $request->sejarah;
                // $youtube = $request->youtube;






                $data = MasterKampus::where('id', $id_kampus)->firstOrFail();
                $keterangan =  json_decode($data->keterangan);


                $arr = array();
                $arr = (object) [
                    'sejarah' => $request->sejarah == '' ? $keterangan->sejarah : $request->sejarah,
                    'visi' => $request->visi == '' ? $keterangan->visi : $request->visi,
                    'misi' => $request->misi == '' ? $keterangan->misi : $request->misi,
                    'slogan' => $request->slogan == '' ? $keterangan->slogan : $request->slogan,
                    'youtube' => $request->youtube == '' ? $keterangan->youtube : $request->youtube
                ];





                $hasil = json_encode($arr);






                // $visi_simpan['visi'] = $visi;
                // $misi_simpan['misi'] = $misi;
                // $sejarah_simpan['sejarah'] = $sejarah;
                // $youtube_simpan['youtube'] = $youtube;


                // $edit_visi = array_replace($keterangan, $visi_simpan);
                // $edit_misi = array_replace($edit_visi, $misi_simpan);
                // $edit_sejarah = array_replace($edit_misi, $misi_simpan);
                // $edit_youtube = array_replace($edit_sejarah, $misi_simpan);



                //  echo "<pre>";
                // print_r($keterangan);


                // echo "<pre>";
                // print_r($misi_simpan);

                //  echo "<pre>";
                // print_r($edit_simpan);


                //  die;


                DB::table('kampus')->where('id_kampus', $id_kampus)
                    ->join('auth', 'kampus.id', '=', 'auth.id_kampus')
                    ->where('kampus.id', $id_kampus)
                    ->update(['keterangan' => $hasil]);
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }



    function master_kampus_propektus_kampus(Request $request)
    {


        try {



            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $jurusan = $request->jurusan;

                $komp = $request->komp;
                $karir = $request->karir;
                $kode = $request->kode;



                $data = MasterKampus::where('id', $id_kampus)->firstOrFail();
                $propektus = (array)json_decode($data->attr_propektus);





                foreach ($propektus as $key1 => $p) {


                    if ($p->kode == $kode) {
                        //update
                        $update[$p->kode] = $key1;
                    } else {
                        //tambah
                        // echo "tambah";
                    }
                }


                error_reporting(0);

                if ($update) {
                    //update



                    $prospek_edit[$update[$kode]] = array(
                        'kode' => $kode,
                        'komp' => $komp,
                        'karir' => $karir,
                    );



                    $edit_simpan = array_replace_recursive($propektus, $prospek_edit);

                    DB::table('kampus')->where('id', $id_kampus)->update(['attr_propektus' => json_encode($edit_simpan)]);
                } else {
                    // tambah 
                    // echo "tambah";

                    $prospek_tambah[] = array(
                        'kode' => $kode,
                        'komp' => $komp,
                        'karir' => $karir,
                    );

                    $prospek_simpan = array_merge($propektus, $prospek_tambah);


                    DB::table('kampus')->where('id', $id_kampus)->update(['attr_propektus' => json_encode($prospek_simpan)]);
                }






                echo json_encode($this->res_insert("sukses"));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }



    function list_transaksi(Request $req)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                $pages = $req->get('pages');
                $search = $req->get('search');
                $limits = $req->get('limit');
                $daris = $req->get('dari');
                $hinggas = $req->get('hingga');
                $statuss = $req->get('status');

                if ($limits) {
                    $limit = $limits;
                } else {
                    $limit = 10;
                }

                //   if($statuss == 0 || $statuss == 1 || $statuss == 99){

                //     $status_no = [$statuss];
                //     if($statuss==1){
                //         $status = ['sukses_formulir'];
                //     }elseif($statuss==0){
                //         $status = ['menunggu_pembayaran_formulir'];
                //     }elseif($statuss==99){
                //         $status = ['dibatalkan_formulir'];
                //     }

                // }else{
                //     // echo "tes";
                //     // die;

                //     $status = ['sukses_formulir','menunggu_pembayaran_formulir'];
                //     $status_no = ['0','1'];
                // }

                // // echo "<pre>";
                // // print_r($status);
                // // die;


                if ($statuss == 0 and $statuss != '') {
                    $where_status = "and JSON_EXTRACT(`status`, '$[*].sts') like  '%menunggu_pembayaran_formulir%'";
                } elseif ($statuss == 1) {
                    $where_status = "and JSON_EXTRACT(`status`, '$[*].sts') like  '%sukses_formulir%'";
                } elseif ($statuss == 99) {
                    $where_status = "and JSON_EXTRACT(`status`, '$[*].sts') like  '%batalkan_formulir%'";
                } elseif ($statuss == 10) {
                    $where_status = "and JSON_EXTRACT(`status`, '$[*].sts') like  '%menunggu_pembayaran_herreg%'";
                } elseif ($statuss == 11) {
                    $where_status = "and JSON_EXTRACT(`status`, '$[*].sts') like  '%sukses_herreg%'";
                } elseif ($statuss == 98) {
                    $where_status = "and JSON_EXTRACT(`status`, '$[*].sts') like  '%batalkan_herreg%'";
                } else {
                    $where_status = "and  JSON_EXTRACT(`status`, '$[*].sts') like  '%menunggu%'";
                }




                if ($pages) {
                    $page = $pages - 1;
                    $pagess = $pages;
                } else {
                    $page = 0;
                    $pagess = 1;
                }

                $offset = $limit * $page;




                if (($daris != '' and $hinggas != '')) {
                    // echo "tes";
                    // die;
                    $where_tgl = "and date(created_at) BETWEEN '" . $daris . "' and '" . $hinggas . "'";
                } else {
                    //  echo "hai";
                    // die;
                    $where_tgl = "";
                }

                $tagihan = DB::select('select *,(select nama from mahasiswa where id_auth = payment.id_auth ) as nama from payment 
                where   id_kampus = "' . $id_kampus . '" 
                 ' . $where_status . '
                 ' . $where_tgl . '
                LIMIT ' . $limit . ' OFFSET ' . $offset . '');


                $count = DB::select('select count(id) as total from payment 
                where   id_kampus = "' . $id_kampus . '" 
                 ' . $where_status . '
                 ' . $where_tgl . '
                ');



                // echo 'select *,(select nama from mahasiswa where id_auth = payment.id_auth ) as nama from payment 
                //   where   id_kampus = "'.$id_kampus.'" 
                //    '.$where_status.'
                //    '.$where_tgl.'
                //   LIMIT '.$limit.' OFFSET '.$offset.'';






                $dari = $pagess;

                $totaldata = $count[0]->total;
                $hingga = ceil($totaldata / $limit);
                $totalhalaman = count($tagihan);



                // $dari = $tagihan->currentPage();
                // $hingga = $tagihan->lastPage();
                // $totaldata = $tagihan->total();
                // $totalhalaman = $tagihan->count();
                // $data = $tagihan->items();


                foreach ($tagihan as $d) {

                    $sts = json_decode($d->status);
                    $detail_tagihan = \DB::table('payment_detail')
                        ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'payment_detail.id_komponen_biaya')
                        ->join('m_komponen', 'm_komponen.id_komponen', '=', 'komponen_biaya.id_komponen')
                        ->where('payment_detail.id_payment', $d->id)
                        ->select('payment_detail.*', 'm_komponen.nama_komponen')
                        ->get();

                    $data_sts = "";
                    foreach ($sts as $s) {


                        $data_sts .= $s->sts;
                        if (count($sts) > 0) {
                            $data_sts .= ",";
                        }
                    }




                    $data2[] = array(
                        'id' => $d->id,
                        'id_kampus' => $d->id_kampus,
                        'id_auth' => $d->id_auth,
                        'nomor_va' => $d->nomor_va,
                        'nama_mahasiswa' => $d->nama,
                        'tanggal_aktif' => $d->tanggal_aktif,
                        'tanggal_expire' => $d->tanggal_expire,
                        'tanggal_batal' => $d->tanggal_batal,
                        'status' => $data_sts,
                        'created_at' => $d->created_at,
                        'total_tagihan' => $d->total_tagihan,
                        'status_bayar' => $d->status_bayar,
                        'tanggal_bayar' => $d->tanggal_bayar,
                        'no_invoice' => $d->no_invoice,
                        'detail' => $detail_tagihan,

                    );

                    //if($data2)

                }

                error_reporting(0);
                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data2));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }



    function simulasi_biaya(Request $request)
    {

        // echo "tes";
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                $semester = $request->semester;
                $jurusan = $request->jurusan;
                $id_gelombang = $request->id_gelombang;

                $label_last = $request->label;
                $biaya_last = $request->biaya;
                $keterangan_last = $request->keterangan;

                $label = explode(',', $label_last);
                $biaya = explode(',', $biaya_last);
                $keterangan = explode(',', $keterangan_last);

                $tahun_ajar = \DB::table('pmb_gelombang')

                    ->where('pmb_gelombang.id_gelombang', $id_gelombang)
                    ->select('pmb_gelombang.tahun_ajar')
                    ->first();



                foreach ($label as $key => $l) {

                    DB::table('komponen_biaya')->insert([
                        "id_komponen"   => $l,
                        "id_kampus"     => $id_kampus,
                        "biaya"         => $biaya[$key],
                        "id_tahun_ajar" => $tahun_ajar->tahun_ajar,
                        "kode_jurusan"  => $jurusan,
                        "status"        => 'semester' . $semester,
                        'id_gelombang'  => $id_gelombang,
                        'keterangan'    => $keterangan[$key],
                    ]);
                }

                echo json_encode($this->res_insert("sukses"));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function gelombang(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                error_reporting(0);
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $id_tahun_ajar = $request->id_tahun_ajar;
                $statuss        = $request->status;

                $data = \DB::table('pmb_gelombang as p')
                    ->join('tahun_ajar as t', 'p.id_tahun_ajar', '=', 't.id')
                    ->where('p.id_kampus', $id_kampus)
                    ->where('t.id', $id_tahun_ajar)
                    ->select('p.*', 't.tahun_ajar');
                // ->get();

                if ($statuss != '') {
                    $data = $data->where('p.status', $statuss)->get();
                } else {
                    $data = $data->get();
                }


                foreach ($data as $d) {

                    $data_jadwal = \DB::table('pmb_jadwal')
                        ->where('pmb_jadwal.id_gelombang', $d->id_gelombang)
                        ->select('*')
                        ->get();



                    $data2[] = array(

                        "id_gelombang" => $d->id_gelombang,
                        "nama_gelombang" => $d->nama_gelombang,
                        'nama_gelombang_tgl' => $d->nama_gelombang . '-' . $this->TanggalIndo($d->tgl_mulai) . '-' . $this->TanggalIndo($d->tgl_selesai),
                        "tgl_mulai" => $d->tgl_mulai,
                        "tgl_selesai" => $d->tgl_selesai,
                        "status" => $d->status,
                        "id_kampus" => $d->id_kampus,
                        "tahun_ajar" => $d->tahun_ajar,
                        "ujian_seleksi" => $d->ujian_seleksi,
                        "data_list" => $data_jadwal,
                    );
                }

                echo json_encode($this->show_data_object($data2));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function edit_gelombang(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                $nama_gelombang = $request->nama_gelombang;
                $tgl_mulai = $request->tgl_mulai;
                $tgl_selesai = $request->tgl_selesai;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $ujian_seleksi = $request->ujian_seleksi;

                $id_gelombang = $request->id_gelombang;
                $status = $request->status;




                $data_simpan = array(
                    'nama_gelombang' => $nama_gelombang,
                    'tgl_mulai' => $tgl_mulai,
                    'tgl_selesai' => $tgl_selesai,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'ujian_seleksi' => $ujian_seleksi,
                    'id_kampus' => $id_kampus,
                    'status' => $status,
                );

                DB::table('pmb_gelombang')->where('id_gelombang', $id_gelombang)->update($data_simpan);

                echo json_encode($this->res_insert('sukses'));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function tambah_gelombang(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                $nama_gelombang = $request->nama_gelombang;
                $tgl_mulai = $request->tgl_mulai;
                $tgl_selesai = $request->tgl_selesai;
                $id_tahun_ajar = $request->id_tahun_ajar;
                $ujian_seleksi = $request->ujian_seleksi;

                $id_gelombang = $request->id_gelombang;
                $status = $request->status;



                $data_simpan = array(
                    'nama_gelombang' => $nama_gelombang,
                    'tgl_mulai' => $tgl_mulai,
                    'tgl_selesai' => $tgl_selesai,
                    'id_tahun_ajar' => $id_tahun_ajar,
                    'ujian_seleksi' => $ujian_seleksi,
                    'id_kampus' => $id_kampus,
                    'status' => $status,
                    'crdt' => date('Y-m-d H:i:s'),
                );

                // echo "<pre>";
                // print_r($data_simpan);
                // die;

                DB::table('pmb_gelombang')->insert($data_simpan);

                echo json_encode($this->res_insert('sukses'));
            });
        } catch (Exception $e) {
            return response()->json(["log" => $e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }

    function list_berkas(Request $req)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $req) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;




                $pages = $req->get('pages');

                $search = $req->get('search');

                $limits = $req->get('limit');

                $daris = $req->get('dari');
                $hinggas = $req->get('hingga');








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

                $datanya = \DB::table('berkas_mahasiswa')->get();


                if ($search) {

                    if ($daris) {
                        $berkas = \DB::table('berkas_mahasiswa as b')
                            ->join('auth as a', 'b.id_auth', '=', 'a.id')
                            ->join('mahasiswa as m', 'm.id_auth', '=', 'a.id')
                            ->where('a.id_kampus', $id_kampus)
                            ->where('c.nama', 'LIKE', '%' . $search . '%')
                            ->whereBetween(DB::raw('DATE(b.created_at)'), [$daris, $hinggas])
                            ->paginate($limit, ['*'], 'page', $page);
                    } else {


                        $berkas = \DB::table('berkas_mahasiswa as b')
                            ->join('auth as a', 'b.id_auth', '=', 'a.id')
                            ->join('mahasiswa as m', 'm.id_auth', '=', 'a.id')
                            ->where('a.id_kampus', $id_kampus)
                            ->where('c.nama', 'LIKE', '%' . $search . '%')
                            ->paginate($limit, ['*'], 'page', $page);


                        // echo "<pre>";
                        // print_r($berkas);

                        // die;
                    }
                } else {

                    if ($daris) {
                        $berkas = \DB::table('berkas_mahasiswa as b')
                            ->join('auth as a', 'b.id_auth', '=', 'a.id')
                            ->join('mahasiswa as m', 'm.id_auth', '=', 'a.id')
                            ->where('a.id_kampus', $id_kampus)
                            ->whereBetween(DB::raw('DATE(b.created_at)'), [$daris, $hinggas])
                            ->paginate($limit, ['*'], 'page', $page);
                    } else {
                        $berkas = \DB::table('berkas_mahasiswa as b')
                            ->join('auth as a', 'b.id_auth', '=', 'a.id')
                            ->join('mahasiswa as m', 'm.id_auth', '=', 'a.id')
                            ->where('a.id_kampus', $id_kampus)

                            ->groupBy('b.id_auth', 'm.nama')
                            ->paginate($limit, ['b.id_auth', 'm.nama'], 'page', $page);
                    }
                }





                $dari = $berkas->currentPage();
                $hingga = $berkas->lastPage();
                $totaldata = $berkas->total();
                $totalhalaman = $berkas->count();
                $data = $berkas->items();

                error_reporting(0);



                // $detail_berkas[] = array(
                //     'id_berkas'=>$b->id,
                //     'nama'=>$b->nama_file,
                //     'status'=>$b->status,
                //     'file'=>asset('public/berkas_mahasiswa/'.$withoutExt.'/'.$b->nama_file),
                //     'created_at'=>$this->TanggalIndo($b->created_at).' - '.date('H:i:s',strtotime($b->created_at)),
                // );

                foreach ($datanya as $b) {
                    $ex = explode('_', $b->nama_file);
                    $withoutExt = substr($ex[1], 0, (strrpos($ex[1], ".")));

                    $data_json[$b->id_auth][] = array(
                        'id_berkas' => $b->id,
                        'nama' => $b->nama_file,
                        'status' => $b->status,
                        'file' => asset('public/berkas_mahasiswa/' . $withoutExt . '/' . $b->nama_file),
                        'created_at' => $this->TanggalIndo($b->created_at) . ' - ' . date('H:i:s', strtotime($b->created_at)),
                    );
                }

                // echo "<pre>";
                // print_r($data_json);
                // die;


                foreach ($berkas as $b) {
                    $data2[] = array(
                        'id_auth' => $b->id_auth,
                        'nama_mahasiswa' => $b->nama,
                        'datanya' => $data_json[$b->id_auth]
                    );
                }

                echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data2));
            });
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



    function update_status_berkas(Request $request)
    {
        try {
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = Mahasiswa::where('id_auth',$payload->id)->firstOrFail();

            $status = $request->status;

            $id_berkas_last = $request->id_berkas;

            $id_berkas = explode(',', $id_berkas_last);

            // echo "<pre>";
            // print_r($id_berkas);

            // die;

            foreach ($id_berkas as $b) {
                DB::table('berkas_mahasiswa')->where('id', $b)->update(['status' => $status]);
            }


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

    function detail_mhs_edit(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $id_mahasiswa        = $request->id_mahasiswa;


                $mhs = DB::table('mahasiswa')
                    ->where('id', $id_mahasiswa)
                    ->select('*')
                    ->first();

                $data_mhs = array(
                    "id" => $mhs->id,
                    "id_auth" => $mhs->id_auth,
                    "nama" => $mhs->nama,
                    "jenis_kelamin" => $mhs->jenis_kelamin,
                    "tanggal_lahir" =>  $mhs->tanggal_lahir,
                    "tempat_lahir" =>  $mhs->tempat_lahir,
                    "nama_ibu" => $mhs->nama_ibu,
                    "agama" => $this->agama($mhs->agama),
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
                    "jurusan1" => $mhs->jurusan1,
                    "jurusan2" => $mhs->jurusan2,
                    "jurusan3" => $mhs->jurusan3,
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

    function update_mahasiswa_riwayat_pribadi(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $id_mahasiswa        = $request->id_mahasiswa;
                $mahasiswa                    = Mahasiswa::where('id', $id_mahasiswa)->first();


                // echo "<pre>";
                // print_r($mahasiswa);
                // die;


                $validator = $this->validate($request, [
                    'nama'          => 'required',
                    // 'jenis_kelamin' => 'required',
                    // 'tanggal_lahir' => 'required',
                    // 'tempat_lahir'  => 'required',

                    // 'agama'         => 'required',
                    // 'kewarganegaraan'   => 'required',
                    // 'nik'               => 'required',
                    // 'nisn'              => 'required',
                    // 'jurusan1'              => 'required',
                    // 'id_gelombang'              => 'required',
                    // // 'jalan'             => 'required',
                    // // 'telp'              => 'required',
                    // // 'dusun'             => 'required',
                    // // 'rt'                => 'required',
                    // // 'rw'                => 'required',
                    // // 'hp'                => 'required',
                    // 'kelurahan'         => 'required',
                    // // 'kode_pos'          => 'required',
                    // 'penerima_kps'      => 'required',
                    // 'kecamatan'         => 'required',
                    // 'jenis_tinggal'     => 'required',
                    // 'alat_transformasi' => 'required'
                ]);

                if (!$validator) {
                    return response()->json($this->res_insert("required"));
                }

                // echo "<pre>";
                //  print_r($mahasiswa);
                //  die;

                $mahasiswa->nama              = $request->nama;
                $mahasiswa->jenis_kelamin     = $request->jenis_kelamin;


                //

                if ($request->tanggal_lahir) {
                    $tanggal_lahir = $request->tanggal_lahir;
                } else {
                    $tanggal_lahir = null;
                }


                $mahasiswa->tanggal_lahir     = $tanggal_lahir;



                $mahasiswa->tempat_lahir      = $request->tempat_lahir;
                $mahasiswa->nama_ibu          = $request->nama_ibu;
                $mahasiswa->agama             = $request->agama;

                $auth               = User::find($mahasiswa->id_auth);
                $auth->nama_lengkap = $request->nama;
                $auth->save();




                $mahasiswa->kewarganegaraan   = $request->kewarganegaraan;
                $mahasiswa->nik               = $request->nik;
                $mahasiswa->nisn              = $request->nisn;
                $mahasiswa->jurusan1              = $request->jurusan1;
                $mahasiswa->jurusan2              = $request->jurusan2;
                $mahasiswa->jurusan3              = $request->jurusan3;
                $mahasiswa->id_gelombang              = $request->id_gelombang;

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
                $mahasiswa->kota         = $request->kota;
                $mahasiswa->jalur_masuk         = $request->jalur_masuk;
                $mahasiswa->jenis_tinggal     = $request->has('jenis_tinggal') ? $request->jenis_tinggal : $mahasiswa->jenis_tinggal;

                if ($request->id_alat_transport) {
                    $id_alat_transport = $request->id_alat_transport;
                } else {
                    $id_alat_transport = null;
                }



                $mahasiswa->id_alat_transport = $id_alat_transport;

                $mahasiswa->updated_at         = date('Y-m-d H:i:s');

                $mahasiswa->save();


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

    function update_mahasiswa_orangtua(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $id_mahasiswa        = $request->id_mahasiswa;
                $mahasiswa                    = Mahasiswa::where('id', $id_mahasiswa)->first();


                $validator = $this->validate($request, [
                    'nama_ayah'          => 'required',
                    // 'jenis_kelamin' => 'required',
                    // 'tanggal_lahir' => 'required',
                    // 'tempat_lahir'  => 'required',

                    // 'agama'         => 'required',
                    // 'kewarganegaraan'   => 'required',
                    // 'nik'               => 'required',
                    // 'nisn'              => 'required',
                    // 'jurusan1'              => 'required',
                    // 'id_gelombang'              => 'required',
                    // // 'jalan'             => 'required',
                    // // 'telp'              => 'required',
                    // // 'dusun'             => 'required',
                    // // 'rt'                => 'required',
                    // // 'rw'                => 'required',
                    // // 'hp'                => 'required',
                    // 'kelurahan'         => 'required',
                    // // 'kode_pos'          => 'required',
                    // 'penerima_kps'      => 'required',
                    // 'kecamatan'         => 'required',
                    // 'jenis_tinggal'     => 'required',
                    // 'alat_transformasi' => 'required'
                ]);

                if (!$validator) {
                    return response()->json($this->res_insert("required"));
                }

                // echo "<pre>";
                //  print_r($mahasiswa);
                //  die;

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



    public function detail_berkas_admin(Request $request)
    {
        try {
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();

            $id_mahasiswa = $request->id_mahasiswa;

            //  $status_foto = $request->status_foto;
            //  $status_kk = $request->status_kk;
            // $status_ktp = $request->status_ktp;
            // $status_ijazah = $request->status_ijazah;


            $mahasiswa = DB::table('mahasiswa')
                ->where('id', $id_mahasiswa)
                ->first();

            // echo "<pre>";
            // print_r($payload->id);
            // die;

            DB::transaction(function () use ($request, $mahasiswa, $payload) {
                if ($request->has('foto')) {
                    $type_foto = $request->file('foto')->getClientMimeType();
                    $file_foto = str_replace(' ', '_', $mahasiswa->nama) . "_foto." . $request->file('foto')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $mahasiswa->id_auth)->where('tipe_file', 'foto')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/foto/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/foto/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }

                    $simpan_foto = $request->file('foto')->move('public/berkas_mahasiswa/foto', $file_foto);
                    $berkas->id_auth    = $mahasiswa->id_auth;
                    $berkas->nama_file  = str_replace(' ', '_', $file_foto);
                    $berkas->tipe_file  = 'foto';
                    $berkas->save();
                }
                if ($request->has('ktp')) {
                    $type_ktp = $request->file('ktp')->getClientMimeType();
                    $file_ktp = str_replace(' ', '_', $mahasiswa->nama) . "_ktp." . $request->file('ktp')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $mahasiswa->id_auth)->where('tipe_file', 'ktp')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/ktp/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/ktp/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }
                    $simpan_ktp = $request->file('ktp')->move('public/berkas_mahasiswa/ktp', $file_ktp);
                    $berkas->id_auth    = $mahasiswa->id_auth;
                    $berkas->nama_file  = str_replace(' ', '_', $file_ktp);
                    $berkas->tipe_file  = 'ktp';
                    $berkas->save();
                }
                if ($request->has('kk')) {
                    $type_kk = $request->file('kk')->getClientMimeType();
                    $file_kk = str_replace(' ', '_', $mahasiswa->nama) . "_kk." . $request->file('kk')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $mahasiswa->id_auth)->where('tipe_file', 'kk')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/kk/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/kk/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }
                    $simpan_kk = $request->file('kk')->move('public/berkas_mahasiswa/kk', $file_kk);
                    $berkas->id_auth    = $mahasiswa->id_auth;
                    $berkas->nama_file  = str_replace(' ', '_', $file_kk);
                    $berkas->tipe_file  = 'kk';
                    $berkas->save();
                }
                if ($request->has('ijazah')) {
                    $type_ijazah = $request->file('ijazah')->getClientMimeType();
                    $file_ijazah = str_replace(' ', '_', $mahasiswa->nama) . "_ijazah." . $request->file('ijazah')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $mahasiswa->id_auth)->where('tipe_file', 'ijazah')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/ijazah/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/ijazah/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }
                    $simpan_ijazah = $request->file('ijazah')->move('public/berkas_mahasiswa/ijazah', $file_ijazah);
                    $berkas->id_auth    = $mahasiswa->id_auth;
                    $berkas->nama_file  = str_replace(' ', '_', $file_ijazah);
                    $berkas->tipe_file  = 'ijazah';
                    $berkas->save();
                }
            });
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

    function detail_berkas(Request $request)
    {
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();
            $id_mahasiswa = $request->id_mahasiswa;

            $mahasiswa = DB::table('mahasiswa')
                ->where('id', $id_mahasiswa)
                ->first();

            $berkas = DB::table('berkas_mahasiswa')
                ->where('id_auth', $mahasiswa->id_auth)
                ->get();



            foreach ($berkas as $b) {
                $ex = explode('_', $b->nama_file);
                $withoutExt = substr($ex[1], 0, (strrpos($ex[1], ".")));
                $detail_berkas[] = array(
                    'id_berkas' => $b->id,
                    'nama' => $b->nama_file,
                    'status' => $b->status,
                    'file' => asset('public/berkas_mahasiswa/' . $b->tipe_file . '/' . $b->nama_file),
                    'created_at' => $this->TanggalIndo($b->created_at) . ' - ' . date('H:i:s', strtotime($b->created_at)),

                );
            }


            echo json_encode($this->show_data_object($detail_berkas));
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



    function kartu_ujian(Request $request)
    {
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();
            $id_mahasiswa = $request->id_mahasiswa;
            $id_auth = $payload->id;
            $id_kampus = $payload->id_kampus;

            $mahasiswa = DB::table('mahasiswa')
                ->where('id_auth', $id_auth)
                ->first();

            $berkas = DB::table('berkas_mahasiswa')
                ->where('id_auth', $id_auth)
                ->where('tipe_file', 'foto')
                ->first();


            $jadwal_ujian_query = DB::select("select jadwal_ujian,id_pmb_jadwal from pmb_jadwal where id_gelombang = '" . $mahasiswa->id_gelombang . "' and tgl_rate_gelombang_mulai <= '" . date($mahasiswa->created_at) . "' and tgl_rate_gelombang_selesai >= '" . date($mahasiswa->created_at) . "'");

            error_reporting(0);
            if ($mahasiswa->pmb_jadwal_ujian) {
                $jadwal_ujian = $mahasiswa->pmb_jadwal_ujian;
            } else {
                $jadwal_ujian = $jadwal_ujian_query[0]->jadwal_ujian;
            }


            $data_json = array(
                'logo' => asset('public/images/' . $this->kampus($id_kampus)->logo_kampus),
                'tahun_ajaran' => $this->cek_tahun_ajar($mahasiswa->id_gelombang)->keterangan,
                'foto' => asset('public/berkas_mahasiswa/foto/' . $berkas->nama_file),
                'nama_mahasiswa' => $mahasiswa->nama,
                'no_pendaftaran' => $mahasiswa->pmb_no_pendaftaran,
                'tanggal_lahir' => $mahasiswa->tanggal_lahir,
                'tempat_lahir' => $mahasiswa->tempat_lahir,
                'jenis_kelamin' => $mahasiswa->jenis_kelamin,
                'pilihan_prodi1' => $this->nama_prodi($mahasiswa->jurusan1),
                'pilihan_prodi2' => $this->nama_prodi($mahasiswa->jurusan2),
                'pilihan_prodi3' => $this->nama_prodi($mahasiswa->jurusan3),
                'jadwal_tes' => $this->DayIndonesia($jadwal_ujian) . ' ' . $this->TanggalIndo($jadwal_ujian),



            );


            DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['jadwal_uji_seleksi' => 1]);
            $total = DB::select('select sum(formulir_pendaftaran+info_pembayaran+lengkapi_berkas+jadwal_uji_seleksi+info_kelulusan) as total from pmb_tahapan_pendaftaran  where id_auth = "' . $payload->id . '"');
            DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['total' => $total[0]->total]);


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


    function cek_bayar(Request $request)
    {
        try {


            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();
            $id_mahasiswa = $request->id_mahasiswa;
            $id_auth = $payload->id;
            $id_kampus = $payload->id_kampus;

            $tipe = $request->tipe;

            if ($tipe == 'formulir') {
                $where_tipe = 'JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_formulir%"';
                $komponen = "formulir";
            } elseif ($tipe == 'herreg') {
                $where_tipe = 'JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%"';
                $komponen = "herreg";
            } elseif ($tipe == 'semua') {
                // $where_tipe = 'JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%"';
                // $komponen = "herreg";

                //                     select (select id from payment where JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_formulir%" limit 1) as formulir,
                // (select id from payment where JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%" limit 1) as herreg  

                // from payment where id_auth = 122 and status_no = 1 limit 1
            } else {
                echo json_encode($this->res_insert("fatal"));
                die;
            }


            $hasil = DB::select('select *  from payment where ' . $where_tipe . '  and id_auth = "' . $id_auth . '"');

            if ($hasil[0]) {

                $status = 1;
                $messege = "sudah bayar '" . $komponen . "'";
            } else {
                $status = 0;
                $messege = "belum bayar '" . $komponen . "'";
            }


            $data_json = array(
                'status' => $status,
                'messege' => $messege,
            );

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

    function tahun_ajar_kampus(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            $pages = $request->get('pages');
            $limits = $request->get('limit');

            if ($pages) {
                $page = $pages;
                $pagess = $pages;
            } else {
                $page = 0;
                $pagess = 1;
            }

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            $offset = $limit * $page;


            $tahun_ajar = DB::table('tahun_ajar')
                ->where('id_kampus', $id_kampus)
                ->paginate($limit, ['*'], 'page', $page);


            // $dari = $pagess;
            // $totaldata = count($tahun_ajar);
            // $hingga = ceil($totaldata / $limit);
            // $totalhalaman = count($tahun_ajar);

            $dari = $tahun_ajar->currentPage();
            $hingga = $tahun_ajar->lastPage();
            $totaldata = $tahun_ajar->total();
            $totalhalaman = $tahun_ajar->count();
            $data = $tahun_ajar->items();

            error_reporting(0);



            echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
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


    function tahun_ajar_kampus_aktif(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            //die;
            $tahun_ajar = DB::table('tahun_ajar')
                ->where('id_kampus', $id_kampus)
                ->where('status', 1)
                ->where('tipe', 'pmb')
                ->get();
            echo json_encode($this->show_data_object($tahun_ajar));
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

    function tahun_ajar_kampus_aktif_siakad(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            //die;
            $tahun_ajar = DB::table('tahun_ajar')
                ->where('id_kampus', $id_kampus)
                ->where('status', 1)
                ->where('tipe', 'siakad')
                ->get();
            echo json_encode($this->show_data_object($tahun_ajar));
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



    function tambah_tahun_ajar_kampus(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            $tahun_ajar = $request->tahun_ajar;
            $keterangan = $request->keterangan;
            $tipe = $request->tipe;
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;
            $status = $request->status;


            $data_simpan = array(
                'tahun_ajar' => $tahun_ajar,
                'keterangan' => $keterangan,
                'id_kampus' => $id_kampus,
                'crdt' => date('Y-m-d H:i:s'),
                'tipe' => $tipe,
                'status' => $status,
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
            );




            $tahun_ajar = DB::table('tahun_ajar')->insert($data_simpan);
            echo json_encode($this->res_insert('sukses'));
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


    function edit_tahun_ajar_kampus(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;
            $tahun_ajar = $request->tahun_ajar;
            $keterangan = $request->keterangan;
            $id_tahun_ajar = $request->id_tahun_ajar;
            $status = $request->status;
            $tipe = $request->tipe;
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;


            $data_simpan = array(
                'tahun_ajar' => $tahun_ajar,
                'keterangan' => $keterangan,
                'id_kampus' => $id_kampus,
                'crdt' => date('Y-m-d H:i:s'),
                'status' => $status,
                'tipe' => $tipe,
                'tgl_awal' => $tgl_awal,
                'tgl_akhir' => $tgl_akhir,
            );




            $tahun_ajar = DB::table('tahun_ajar')->where('id', $id_tahun_ajar)->update($data_simpan);
            echo json_encode($this->res_insert('sukses'));
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


    function pmb_jadwal(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            $id_gelombang = $request->id_gelombang;
            $id_tahun_ajar = $request->id_tahun_ajar;



            $pmb_jadwal = DB::table('pmb_jadwal')->where('id_gelombang', $id_gelombang)->get();


            foreach ($pmb_jadwal as $s) {
                $data_json[] = array(
                    "id_pmb_jadwal" => $s->id_pmb_jadwal,
                    "tahun_ajar" => $this->cek_tahun_ajar($s->id_gelombang)->tahun_ajar,
                    "nama_gelombang" => $this->cek_tahun_ajar($s->id_gelombang)->nama_gelombang,
                    "id_gelombang" => $s->id_gelombang,
                    "jadwal_ujian" => $this->TanggalIndo($s->jadwal_ujian),
                    "tgl_pengumuman" => $this->TanggalIndo($s->tgl_pengumuman),
                    "tgl_rate_gelombang_mulai" => $this->TanggalIndo($s->tgl_rate_gelombang_mulai),
                    "tgl_rate_gelombang_selesai" => $this->TanggalIndo($s->tgl_rate_gelombang_selesai),


                    "tgl_jadwal_ujian" => $s->jadwal_ujian,
                    "tgl_tgl_pengumuman" => $s->tgl_pengumuman,
                    "tgl_tgl_rate_gelombang_mulai" => $s->tgl_rate_gelombang_mulai,
                    "tgl_tgl_rate_gelombang_selesai" => $s->tgl_rate_gelombang_selesai,
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


    function tambah_pmb_jadwal(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            $id_gelombang = $request->id_gelombang;
            $jadwal_ujian = $request->jadwal_ujian;
            $tgl_pengumuman = $request->tgl_pengumuman;

            $tgl_rate_mulai = $request->tgl_rate_mulai;
            $tgl_rate_selesai = $request->tgl_rate_selesai;



            $data_simpan = array(
                'id_gelombang' => $id_gelombang,
                'jadwal_ujian' => $jadwal_ujian,
                'tgl_pengumuman' => $tgl_pengumuman,
                'tgl_rate_gelombang_mulai' => $tgl_rate_mulai,
                'tgl_rate_gelombang_selesai' => $tgl_rate_selesai,
                'crdt' => date('Y-m-d H:i:s'),

            );




            $tahun_ajar = DB::table('pmb_jadwal')->insert($data_simpan);
            echo json_encode($this->res_insert('sukses'));
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


    function edit_pmb_jadwal(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            $id_gelombang = $request->id_gelombang;
            $jadwal_ujian = $request->jadwal_ujian;
            $tgl_pengumuman = $request->tgl_pengumuman;

            $tgl_rate_mulai = $request->tgl_rate_mulai;
            $tgl_rate_selesai = $request->tgl_rate_selesai;
            $id_pmb_jadwal = $request->id_pmb_jadwal;



            $data_simpan = array(
                'id_gelombang' => $id_gelombang,
                'jadwal_ujian' => $jadwal_ujian,
                'tgl_pengumuman' => $tgl_pengumuman,
                'tgl_rate_gelombang_mulai' => $tgl_rate_mulai,
                'tgl_rate_gelombang_selesai' => $tgl_rate_selesai,
                'crdt' => date('Y-m-d H:i:s'),

            );




            $tahun_ajar = DB::table('pmb_jadwal')->where('id_pmb_jadwal', $id_pmb_jadwal)->update($data_simpan);
            echo json_encode($this->res_insert('sukses'));
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


    function kelas_aktif(Request $request)
    {
        error_reporting(0);
        try {
            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            //$status = $request->get('status');
            $status = 1;

            $kelas = DB::table('conf_kumkod')->where('id_kampus', $id_kampus);


            //if($status != ''){
            $kelas = $kelas->where('status', $status)->get();
            // }else{
            //     $kelas = $kelas->get();
            // }


            echo json_encode($this->show_data_object($kelas));
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
    function kelas(Request $request)
    {
        error_reporting(0);
        try {
            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            $statuss = $request->get('status');
            $searchh = $request->get('search');
            $pages = $request->get('pages');
            $limits = $request->get('limit');


            if ($pages) {
                $page = $pages;
                $pagess = $pages;
            } else {
                $page = 0;
                $pagess = 1;
            }

            if ($limits) {
                $limit = $limits;
            } else {
                $limit = 10;
            }

            $offset = $limit * $page;

            if (isset($statuss)) {
                $status = $statuss == '1' ? '1' : '0';
            } else {
                $status = 'null';
            }

            if ($searchh) {
                $search = $searchh;
            } else {
                $search = "";
            }


            $kelas = DB::table('conf_kumkod');


            if ($search) {
                $kelass = $kelas->where('nama', 'LIKE', '%' . $search . '%')->paginate($limit, ['*'], 'page', $page);
            } else {
                $kelass = $kelas->paginate($limit, ['*'], 'page', $page);
            }

            if ($status == '1' || $status == '0') {
                if ($status == '1') {
                    $kelass = $kelas->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                } elseif ($status == '0') {
                    $kelass = $kelas->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                }
            } else {
                $kelass = $kelas->paginate($limit, ['*'], 'page', $page);
            }


            $dari = $kelass->currentPage();
            $hingga = $kelass->lastPage();
            $totaldata = $kelass->total();
            $totalhalaman = $kelass->count();
            $data = $kelass->items();


            echo json_encode($this->show_data_object_pagination($dari, $hingga, $totaldata, $totalhalaman, $data));
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

    function kelas_edit(Request $request)
    {
        error_reporting(0);
        try {

            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');

            $id_auth   = $payload->id;
            $id_kampus = $payload->id_kampus;

            $id_kelas = $request->id_kelas;
            $validator = $this->validate($request, [
                'id_kelas' => 'required',
                'nama'          => 'required',
                'keterangan'          => 'required',
                'status'          => 'required',
            ]);


            $update_kelas = array(
                // 'jenis' => $request->jenis,
                // 'kelompok' => $request->kelompok,
                'nama' => $request->nama,
                // 'singkatan' => $request->singkatan,
                'keterangan' => $request->keterangan,
                // 'urutan' => $request->urutan,
                'status' => $request->status,
                // 'name_status' => $request->name_status,
                'id_kampus' => $id_kampus
            );




            $tahun_ajar = DB::table('conf_kumkod')->where('id', $id_kelas)->update($update_kelas);
            echo json_encode($this->res_insert('sukses'));
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

                $kodee = $req->get('kode');
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

                if ($kodee) {
                    $kode = $kodee;
                } else {
                    $kode = '';
                }

                if (isset($statuss)) {
                    $status = $statuss == '1' ? '1' : '0';
                } else {
                    $status = 'null';
                }

                $program_studii = DB::table('m_program_studi')
                    ->where('id_kampus', $id_kampus);
                if ($search) {
                    $program_studi = $program_studii->where('nama_prodi', 'LIKE', '%' . $search . '%')->paginate($limit, ['*'], 'page', $page);
                } else {
                    $program_studi = $program_studii->paginate($limit, ['*'], 'page', $page);
                }

                if ($kode) {
                    $program_studi = $program_studii->where('kode', $kode)->paginate($limit, ['*'], 'page', $page);
                } else {
                    $program_studi = $program_studii->paginate($limit, ['*'], 'page', $page);
                }

                if ($status == '1' || $status == '0') {
                    if ($status == '1') {
                        $program_studi = $program_studii->where('status', '1')->paginate($limit, ['*'], 'page', $page);
                    } elseif ($status == '0') {
                        $program_studi = $program_studii->where('status', '0')->paginate($limit, ['*'], 'page', $page);
                    }
                } else {
                    $program_studi = $program_studii->paginate($limit, ['*'], 'page', $page);
                }

                $dari = $program_studi->currentPage();
                $hingga = $program_studi->lastPage();
                $totaldata = $program_studi->total();
                $totalhalaman = $program_studi->count();
                $data = $program_studi->items();

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
    // function tambah_program_studi(Request $request)
    // {
    //     try {
    //         $headers = apache_request_headers(); //get header
    //         $request->headers->set('Authorization', $headers['Authorization']);

    //         $payload            = JWTAuth::parseToken()->getPayload()->get('data');
    //         DB::transaction(function () use ($payload, $request) {
    //             $id_kampus = $payload->id_kampus;

    //             $data_simpan = array(
    //                 'kode' => $request->kode,
    //                 'nama_prodi' => $this->just_nama_prodi($request->kode),
    //                 'ka_prodi' => $request->ka_prodi,
    //                 'tanggal_ban_sk' => $request->tanggal_ban_sk,
    //                 'no_sk_ban_pt' => $request->no_sk_ban_pt,
    //                 'sks_lulus' => $request->sks_lulus,
    //                 'akre' => $request->akreditasi,
    //                 'id_kampus' => $id_kampus,
    //                 'crdt' => date('Y-m-d H:i:s'),
    //                 'status' => $request->status,
    //                 'kouta' => $request->kouta,
    //                 'id_kelas' => $request->id_kelas,
    //                 'kelas' => $this->nama_kelas($request->id_kelas),
    //                 'id_kelompok_jenjang' => $request->id_kelompok_jenjang
    //             );

    //             DB::table('m_program_studi')->insert($data_simpan);
    //         });

    //         return response()->json($this->res_insert("sukses"));
    //     } catch (Exception $e) {
    //         return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
    //         // return response()->json($this->res_insert("fatal"));
    //     } catch (TokenExpiredException $e) {
    //         return response()->json($this->res_insert("token_expired"));
    //     } catch (TokenInvalidException $e) {
    //         return response()->json($this->res_insert("token_invalid"));
    //     } catch (JWTException $e) {
    //         return response()->json($this->res_insert("token_absent"));
    //     }
    // }
    // function edit_program_studi(Request $request)
    // {
    //     try {
    //         $headers = apache_request_headers(); //get header
    //         $request->headers->set('Authorization', $headers['Authorization']);

    //         $payload            = JWTAuth::parseToken()->getPayload()->get('data');
    //         DB::transaction(function () use ($payload, $request) {
    //             $id_kampus = $payload->id_kampus;

    //             $data_simpan = array(
    //                 // 'kode' => $request->kode,
    //                 // 'nama_prodi' => $this->just_nama_prodi($request->kode),
    //                 // 'akre' => $request->akreditasi,
    //                 // 'id_kampus' => $id_kampus,
    //                 // 'crdt' => date('Y-m-d H:i:s'),
    //                 'ka_prodi' => $request->ka_prodi,
    //                 'status' => $request->status,
    //                 'kouta' => $request->kuota
    //                 // 'kouta' => $request->kouta,
    //                 // 'id_kelas' => $request->id_kelas,
    //                 // 'kelas' => $this->nama_kelas($request->id_kelas),
    //                 // 'id_kelompok_jenjang' => $request->id_kelompok_jenjang
    //             );
    //             DB::table('m_program_studi')->where('id_kelas', $request->id_kelas)->where('kode', $request->kode)->where('id_kelompok_jenjang', $request->id_kelompok_jenjang)->update($data_simpan);
    //             $hasilnya = DB::table('m_program_studi')->where('id_kelas', $request->id_kelas)->where('kode', $request->kode)->where('id_kelompok_jenjang', $request->id_kelompok_jenjang)->get()[0];

    //             $check = json_decode(DB::table('kampus')->where('id', $id_kampus)->select('prodi')->first()->prodi);
    //             foreach ($check as $key => $c) {
    //                 if ($c->kode == $request->kode and $c->id_kelas == $request->id_kelas and $c->id_kelompok_jenjang == $request->id_kelompok_jenjang) {
    //                     $index_fix[] = $key;
    //                 }
    //             }


    //             $update = DB::update("UPDATE `kampus` set `prodi` =  JSON_REPLACE(`prodi`,
    //             '$[$index_fix[0]].akre', '$hasilnya->akre',
    //             '$[$index_fix[0]].kode', '$hasilnya->kode',
    //             '$[$index_fix[0]].nama', '$hasilnya->nama_prodi',
    //             '$[$index_fix[0]].id_kelas', '$hasilnya->id_kelas',
    //             '$[$index_fix[0]].kelas', '$hasilnya->kelas',
    //             '$[$index_fix[0]].id_kelompok_jenjang', '$hasilnya->id_kelompok_jenjang',
    //             '$[$index_fix[0]].kuota', '$hasilnya->kouta')
    //             where `id` = $id_kampus ");
    //         });

    //         return response()->json($this->res_insert("sukses"));
    //     } catch (Exception $e) {
    //         return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
    //         // return response()->json($this->res_insert("fatal"));
    //     } catch (TokenExpiredException $e) {
    //         return response()->json($this->res_insert("token_expired"));
    //     } catch (TokenInvalidException $e) {
    //         return response()->json($this->res_insert("token_invalid"));
    //     } catch (JWTException $e) {
    //         return response()->json($this->res_insert("token_absent"));
    //     }
    // }
    // function hapus_program_studi(Request $request)
    // {
    //     try {
    //         $headers = apache_request_headers(); //get header
    //         $request->headers->set('Authorization', $headers['Authorization']);

    //         $payload            = JWTAuth::parseToken()->getPayload()->get('data');
    //         DB::transaction(function () use ($payload, $request) {
    //             $id_kampus = $payload->id_kampus;
    //             DB::table('m_program_studi')->where('id_program_studi', $request->id_program_studi)->delete();
    //         });

    //         return response()->json($this->res_insert("sukses"));
    //     } catch (Exception $e) {
    //         return response()->json(["data" => $request->all(), "log" => $e->getMessage()]);
    //         // return response()->json($this->res_insert("fatal"));
    //     } catch (TokenExpiredException $e) {
    //         return response()->json($this->res_insert("token_expired"));
    //     } catch (TokenInvalidException $e) {
    //         return response()->json($this->res_insert("token_invalid"));
    //     } catch (JWTException $e) {
    //         return response()->json($this->res_insert("token_absent"));
    //     }
    // }
}
