<?php

namespace App\Http\Controllers;

use App\Models\KomponenBiaya;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Traits\utility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TagihanMahasiswaController extends Controller
{
    use utility;


    function edit_penentuan_tarif(Request $req){
          //$data_json = [];


          try {
           // error_reporting(0);
              // $headers = apache_request_headers(); //get header
              // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$req){
                  $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $kode_jurusan = $req->kode_jurusan;
                $kelas = $req->kelas;
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
                    ->get();




                    // echo "<pre>";
                    // print_r($jenis_tagihan);
                    // die;

               
                foreach ($jenis_tagihan as $j) {

                    $cicilan = DB::select("select max(k.cicilan) as cicilan from komponen_biaya as k where k.id_komponen=" . $j->id_komponen . " and k.kode_jurusan = '" . $kode_jurusan . "' and k.id_kampus =  " . $id_kampus . "");

                    $biaya = DB::select("select sum(k.biaya) as biaya from komponen_biaya as k where k.id_komponen=" . $j->id_komponen . " and k.kode_jurusan = '" . $kode_jurusan . "' and k.id_kampus =  " . $id_kampus . "");

                
                     $datanya = DB::table('komponen_biaya as k')
                    ->select('k.jenis_pembayaran','k.id_komponen_biaya')
                    ->where('k.id_komponen', $j->id_komponen)
                     ->where('k.id_kampus', $id_kampus)
                      ->where('k.kode_jurusan', $kode_jurusan)
                    ->first();

                    $status = DB::table('payment_detail as k')
                    ->select('id')
                    ->where('k.id_komponen_biaya', $datanya->id_komponen_biaya)
                     
                    ->first();

                    if($status->id){
                        $status_cek = 1;
                    }else{
                        $status_cek = 0;
                    }


                    $data_json[] = array(
                        'id_komponen' => $j->id_komponen,
                        'nama_komponen' => $this->nama_komponen($j->id_komponen),
                        'id_tahun_ajar' => $this->tahun_ajar($id_tahun_ajar)->tahun_ajar,
                        'id_gelombang' => $this->cek_tahun_ajar($id_gelombang)->nama_gelombang,
                        'id_kelas' => $kelas,
                        'kelas'=>$this->kelas($id_kampus,$kelas),
                        'jenis_pembayaran' => $datanya->jenis_pembayaran,
                        'cicilan' => $cicilan[0]->cicilan,
                        'biaya' => $biaya[0]->biaya,
                        'status'=>$status_cek,

                       
                    );
                }

                echo json_encode($this->show_data_object($data_json));

                
              
               

            });
             

           
        } catch (Exception $e) {
             return response()->json(["data"=>$req->all(), "log"=>$e->getMessage()]);
           // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function simpan_edit_penentuan_tarif(Request $req){
          //$data_json = [];


          try {
           // error_reporting(0);
              // $headers = apache_request_headers(); //get header
              // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$req){
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
                            ->select('id_komponen_biaya','id_komponen', 'id_tahun_ajar', 'id_gelombang', 'id_kelas', 'jenis_pembayaran')
                            ->where('kode_jurusan', $kode_jurusan)
                            ->where('id_kampus', $id_kampus)
                            ->where('id_kelas', $id_kelas)
                            ->where('id_tahun_ajar', $id_tahun_ajar)
                            ->where('id_gelombang', $id_gelombang)
                            ->where('id_kelompok_jenjang', $id_kelompok_jenjang)
                            ->whereIn('id_komponen',$jenis_tagihan)
                            
                            ->get();

                            foreach($hapus as $h){
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
                                'kode_jurusan'=>$kode_jurusan,
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
                            'kode_jurusan'=>$kode_jurusan,

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
             return response()->json(["data"=>$req->all(), "log"=>$e->getMessage()]);
           // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }



    // insert
                // foreach ($cicilan as $key => $c) {

                //     if ($c >= 2) {

                //         $datanya_cicilan = $this->nourut($c);


                //         foreach ($datanya_cicilan as $ci) {
                //             $datanya[] = array(
                //                 'id_kampus' => $id_kampus,
                //                 'id_komponen' => $jenis_tagihan[$key],
                //                 'id_tahun_ajar' => $id_tahun_ajar,
                //                 'biaya' => $nominal,
                //                 // 'kode_prodi'=>$kode_prodi[$key],
                //                 'id_gelombang' => $id_gelombang,
                //                 'id_kelas' => $id_kelas,
                //                 'cicilan' => $cicilan,
                //                 'jenis_pembayaran' => $jenis_pembayaran[$key],
                //                 'id_kelompok_jenjang' => $id_kelompok_jenjang,
                //                 'status' => 1,
                //                 'crdt' => date('Y-m-d H:i:s'),
                //                 'kode_jurusan'=>$kode_jurusan,
                //                 'cicilan' => $ci,
                //                 'biaya' => $nominal[$key] / $c,
                //             );
                //         }

                //         $data_cicilan[] = array(

                //             'biaya' => $nominal[$key],
                //             'cicilan' => $c,
                //             'key' => $key,
                //             'listdata' => $datanya

                //         );
                //     }
                // }

                // foreach ($jenis_tagihan as $key => $j) {

                //     if ($cicilan[$key] <= 1) {
                //         $data_tetap[] = array(
                //             'id_kampus' => $id_kampus,
                //             'id_komponen' => $jenis_tagihan[$key],
                //             'id_tahun_ajar' => $id_tahun_ajar,
                //             'biaya' => $nominal[$key],
                //             // 'kode_prodi'=>$kode_prodi[$key],
                //             'id_gelombang' => $id_gelombang,
                //             'id_kelas' => $id_kelas,
                //             'cicilan' => $cicilan[$key],
                //             'jenis_pembayaran' => $jenis_pembayaran[$key],
                //             'id_kelompok_jenjang' => $id_kelompok_jenjang,
                //             'status' => 1,
                //             'crdt' => date('Y-m-d H:i:s'),
                //             'kode_jurusan'=>$kode_jurusan,

                //         );
                //     }
                // }


                // // echo "<pre>";
                // // print_r($data_tetap);
                // // die;

                // //echo json_encode($data_tetap);

                // $data_fix2 = (array) array_merge($data_tetap, $datanya);

                // if ($data_fix2) {
                //     $data_fix = $data_fix2;
                // } elseif ($data_tetap) {
                //     $data_fix = $data_tetap;
                // } else {
                //     $data_fix = $datanya;
                // }

               

                // DB::table('komponen_biaya')->insert($data_fix);

                // echo json_encode($this->res_insert("sukses"));



   
   


}
