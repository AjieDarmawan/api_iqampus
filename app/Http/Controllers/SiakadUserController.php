<?php

namespace App\Http\Controllers;

use App\Models\MasterKampus;
use App\Models\BerkasMahasiswa;


use App\Models\Mahasiswa;
use App\Models\Payment;
use App\Models\PaymentDetail;
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

class SiakadUserController extends Controller
{
    use utility;
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    function tagihan_mahasiswa(Request $request)
    {


        error_reporting(0);
        try {
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //$tahun_ajar = DB::table('conf_kumkod')->where('id', $id_kelas)->update($update_kelas);
            //echo json_encode($this->res_insert('sukses'));

            $id_kampus = $payload->id_kampus;

            $id_auth = $payload->id;

            $mhs = DB::table('auth')
                ->join('mahasiswa', 'auth.id', '=', 'mahasiswa.id_auth')
                ->where('auth.id', $id_auth)
                ->select('mahasiswa.id')
                ->first();

            $id_mahasiswa = $mhs->id;



            // $tagihan_grup = DB::table('tagihan_mahasiswa')
            //        ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'tagihan_mahasiswa.id_komponen_biaya')
            //        ->join('m_komponen', 'm_komponen.id_komponen', '=', 'komponen_biaya.id_komponen')
            //        ->where('tagihan_mahasiswa.id_mahasiswa', $mhs->id)
            //        ->select('m_komponen.nama_komponen','m_komponen.id_komponen')
            //        ->orderBy('tagihan_mahasiswa.cicilan','asc')
            //        ->groupBy('m_komponen.nama_komponen','m_komponen.id_komponen')
            //        ->get();


            //        foreach($tagihan_grup as $tg){
            //            $tagihan_grup_data[$tg->id_komponen] = $tg->nama_komponen;
            //        }

            //        // echo "<pre>";
            //        // print_r($tagihan_grup_data);
            //        // die;






            // $tagihan = DB::table('tagihan_mahasiswa')
            //        ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'tagihan_mahasiswa.id_komponen_biaya')
            //        ->join('m_komponen', 'm_komponen.id_komponen', '=', 'komponen_biaya.id_komponen')
            //        ->where('tagihan_mahasiswa.id_mahasiswa', $mhs->id)
            //        ->select('tagihan_mahasiswa.*','m_komponen.nama_komponen','m_komponen.id_komponen')
            //        ->orderBy('tagihan_mahasiswa.cicilan','asc')
            //        ->get();

            //         foreach($tagihan as $t){
            //            $tagihan_data[$tagihan_grup_data[$t->id_komponen]][] = array(
            //                 "id_tagihan_mahasiswa"=> $t->id_tagihan_mahasiswa,
            //                "id_komponen_biaya"=>  $t->id_komponen_biaya,
            //                "id_mahasiswa"=> $t->id_mahasiswa,
            //                "nominal"=>  $t->nominal,
            //                "status"=>  $t->status,
            //                "id_parent"=>  $t->id_parent,
            //                "crdt"=>  $t->crdt,
            //                "id_tahun_ajar"=>  $t->id_tahun_ajar,
            //                "tgl_bayar"=>  $t->tgl_bayar,
            //                "cicilan"=>  $t->cicilan,
            //                "tgl_akhir_dispensasi"=>  $t->tgl_akhir_dispensasi,
            //                "kode_jurusan"=>  $t->kode_jurusan,
            //                "id_kelas"=>  $t->id_kelas,
            //                "potongan"=>  $t->potongan,
            //                "id_potongan"=>  $t->id_potongan,
            //                "nama_komponen"=>  $t->nama_komponen,
            //            );
            //        }

            $tagihan_mhs = DB::select("select tgl_bayar,id_tagihan_mahasiswa,(select nama from mahasiswa where id = tagihan_mahasiswa.id_mahasiswa) as nama_mahasiswa,tgl_akhir_dispensasi,potongan,id_tahun_ajar,id_mahasiswa,status,kode_jurusan,id_kelas,id_komponen_biaya,cicilan,nominal,(select id_komponen from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as id_komponen,(select jenis_pembayaran from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as jenis_pembayaran from `tagihan_mahasiswa` where id_mahasiswa = '" . $id_mahasiswa . "' and status = '0' ");

            


               






            $jenis_tagihan = DB::select("select (select id_komponen from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as id_komponen,(select jenis_pembayaran from komponen_biaya as k where k.id_komponen_biaya = tagihan_mahasiswa.id_komponen_biaya) as jenis_pembayaran from `tagihan_mahasiswa` where id_mahasiswa = '" . $id_mahasiswa . "' 
                    GROUP by id_komponen,jenis_pembayaran");

             $checkout = DB::table('payment')
                   ->join('payment_detail', 'payment_detail.id_payment', '=', 'payment.id')
                    ->join('tagihan_mahasiswa', 'tagihan_mahasiswa.id_komponen_biaya', 'payment_detail.id_komponen_biaya')
                    ->where('payment.id_auth', $id_auth)
                    ->select('payment.status_no')
                    ->orderBy('payment.nomor_va','desc')
                    ->first();

               if ($checkout->status_no == '0') {
                   
                    $status_checkout = 1;
                } else if ($checkout->status_no == '1') {
                    
                    $status_checkout = 0;
                } elseif ($checkout->status_no == '99') {
                    
                    $status_checkout = 0;
                } else {
                    
                    $status_checkout = 0;
                }



            foreach ($tagihan_mhs as $m) {

                 $hasil = DB::table('payment')
                    ->join('payment_detail', 'payment_detail.id_payment', '=', 'payment.id')
                    ->join('tagihan_mahasiswa', 'tagihan_mahasiswa.id_komponen_biaya', 'payment_detail.id_komponen_biaya')
                    ->where('tagihan_mahasiswa.id_tagihan_mahasiswa', $m->id_tagihan_mahasiswa)
                    ->select('payment.status_no')
                    ->first();

                if ($hasil->status_no == '0') {
                    $status_payment = 'pending';
                    
                } else if ($hasil->status_no == '1') {
                    $status_payment = 'berhasil';
                    
                } elseif ($hasil->status_no == '99') {
                    $status_payment = 'dibatalkan';
                    
                } else {
                    $status_payment = 'payment belum dibuat';
                    
                }


                




                $data_json[$m->id_komponen][] = array(
                    'id_tagihan_mahasiswa' => $m->id_tagihan_mahasiswa,
                    'status' => $m->status,
                    'status_payment' => $status_payment,
                    // 'id_komponen'=>$m->id_komponen,
                    // 'nama_komponen'=>$this->nama_komponen($m->id_komponen),
                    'cicilan' => $m->cicilan,
                    'nominal' => $m->nominal,
                    'waktu_pembayaran' => $m->tgl_bayar,
                    'tanggal_akhir_dispensasi' => $m->tgl_akhir_dispensasi,
                    'potongan' => $m->potongan,
                    'jenis_pembayaran' => $m->jenis_pembayaran
                );
            }

            $data_jenis_tagihan = [];
            foreach ($jenis_tagihan as $j) {

                if($data_json[$j->id_komponen]){
                   $data_jenis_tagihan[] = array(
                    'nama_komponen' => $this->nama_komponen($j->id_komponen), 
                    'jenis_pembayaran' => $j->jenis_pembayaran, 
                    'datanya' => $data_json[$j->id_komponen],

                );
                }
                
            }

            $data = array(
                'status_checkout'=>$status_checkout,
                'id_mahasiswa' => $tagihan_mhs[0]->id_mahasiswa,
                'nama_mahasiswa' => $tagihan_mhs[0]->nama_mahasiswa,
                'tahun_ajar' => $this->tahun_ajar($tagihan_mhs[0]->id_tahun_ajar)->tahun_ajar,
                'kode_jurusan' => $this->nama_prodi($tagihan_mhs[0]->kode_jurusan),
                'kelas' => $this->kelas($id_kampus, $tagihan_mhs[0]->id_kelas),
                'id_tahun_ajar' => $tagihan_mhs[0]->id_tahun_ajar,
                'kode_jurusan' => $tagihan_mhs[0]->kode_jurusan,
                'listdata' => $data_jenis_tagihan,
            );


            echo json_encode($this->show_data_object($data));
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

    public function bayar_tagihan(Request $request)
    {


        // echo $this->buat_nomor();
        // die;

        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                // $id_auth        = 116;
                // $id_kampus      = 51;

                $mhs = DB::table('mahasiswa')
                    ->where('mahasiswa.id_auth', $id_auth)
                    ->select('id_gelombang', 'jurusan1', 'id')
                    ->first();



                //$komponen_biaya = $request->komponen_biaya;

                // echo "<pre>";
                // print_r($komponen_biaya);

                // die;


                $tanggal_aktif  = date('Y-m-d');
                $tanggal_expire = date('Y-m-d', strtotime("+1 day"));

                $payment                    = new Payment();
                $payment->id_auth           = $id_auth;
                $payment->id_kampus         = $id_kampus;
                $payment->tanggal_aktif     = $tanggal_aktif;
                $payment->tanggal_expire    = $tanggal_expire;
                $payment->tanggal_batal     = null;
                $payment->nomor_va          = $this->buat_nomor();
                $payment->no_invoice          = $this->no_invoice();
                $payment->status            = 'menunggu_pembayaran';
                $payment->status_no            = 0;
                $payment->created_at        = date('Y-m-d H:i:s');

                $payment->channel        = $request->channel;
                $payment->save();
                $total_payment = 0;
                $sts = [];
                foreach ($request->id_tagihan_mahasiswa as $id_tagihan_mahasiswa) {
                    // $komponen = KomponenBiaya::where('id_komponen_biaya',$komponen)->firstOrFail();
                    $tagihan_mhs = DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa', $id_tagihan_mahasiswa)->where('id_mahasiswa', $mhs->id)->first();

                    $payment_detail                     = new PaymentDetail();
                    $payment_detail->id_payment         = $payment->id;
                    $payment_detail->id_komponen_biaya  = $tagihan_mhs->id_komponen_biaya;
                    $payment_detail->biaya              = $tagihan_mhs->nominal;
                    $payment_detail->save();

                    $total_payment += $payment_detail->biaya;


                    // echo "<pre>";
                    // print_r($komponen);

                   
                }

                 $sts[] = array('sts' => "menunggu_pembayaran_kuliah");
                $data_total = array(
                    'total_tagihan' => $total_payment,
                    'status' => json_encode($sts)
                );
                DB::table('payment')->where('id', $payment->id)->update($data_total);
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
    public function update_status_tagihan(Request $request)
    {
        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                $mhs = DB::table('mahasiswa')
                    ->where('mahasiswa.id_auth', $id_auth)
                    ->select('id_gelombang', 'jurusan1', 'id')
                    ->first();


                $status = $request->status;
                $id_payment = $request->id_payment;

                $update = DB::table('payment as p')
                    ->where('p.id_auth', $id_auth)
                    ->where('p.id_kampus', $id_kampus)
                    ->where('p.id', $id_payment);

                $update_tagihan = $update->join('payment_detail as pd', 'pd.id_payment', '=', 'p.id')
                    ->join('tagihan_mahasiswa as tm', 'tm.id_komponen_biaya', '=', 'pd.id_komponen_biaya');

                if ($status == '1') {

                    $sts[] = array('sts' => "sukses_bayar");
                    $update->update([
                        'p.tanggal_bayar' => date('Y-m-d H:i:s'),
                        'p.status_no' => '1',
                        'p.status' => json_encode($sts)
                    ]);
                    $update_tagihan->update([
                        'tm.status' => '1',
                        'tm.tgl_bayar' => date('Y-m-d H:i:s')
                    ]);
                } else if ($status == '99') {
                    $sts[] = array('sts' => "dibatalkan");
                    $update->update([
                        'p.tanggal_batal' => date('Y-m-d H:i:s'),
                        'p.tanggal_bayar' => null,
                        'p.status_no' => '99',
                        'p.status' => json_encode($sts)
                    ]);
                    $update_tagihan->update([
                        'tm.status' => '0',
                        'tm.tgl_bayar' => null
                    ]);
                } else {
                    $sts[] = array('sts' => "menunggu_pembayaran_kuliah");
                    $update->update([
                        'p.tanggal_batal' => null,
                        'p.tanggal_bayar' => null,
                        'p.status_no' => '0',
                        'p.status' => json_encode($sts)
                    ]);
                    $update_tagihan->update([
                        'tm.status' => '0',
                        'tm.tgl_bayar' => null
                    ]);
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

     public function riwayat_payment(Request $request){

      // $a =  PaymentDetail::where('id_payment',189)->select('*',DB::select('select id from payment limit 1')[0])->get();

      // echo "<pre>";
      // print_r($a);
      //   die;
      


              try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                       error_reporting(0);
                           // $payment = Payment::where('id_auth',$id_auth)->where('status_no',0)->get();   

                    $payment = DB::select('select *  from payment where  JSON_EXTRACT(`status`, "$[*].sts") like  "%menunggu_pembayaran_kuliah%" and status_no = 0 and id_auth="'.$id_auth.'" order by nomor_va desc');


                   




                    foreach($payment as $p){

                            $payment_detail = DB::select('select (select m.nama_komponen from komponen_biaya as k join m_komponen as m on k.id_komponen = m.id_komponen  where k.id_komponen_biaya = payment_detail.id_komponen_biaya)as nama_komponen, payment_detail.* from payment_detail where id_payment = "'.$p->id.'"');

                        $data[] = array(
                                "id"=> $p->id,
                                "id_kampus"=> $p->id_kampus,
                                "id_auth"=> $p->id_auth,
                                "nomor_va"=> $p->nomor_va,
                                "tanggal_aktif"=> $p->tanggal_aktif,
                                "tanggal_expire"=> $p->tanggal_expire,
                                "tanggal_batal"=> $p->tanggal_batal,
                               
                                //"created_at": "2022-12-21 15:45:08",
                                "total_tagihan"=> $p->total_tagihan,
                                "status_bayar"=> $p->status_bayar,
                                "tanggal_bayar"=>$p->tanggal_bayar,
                                "no_invoice"=> $p->no_invoice,
                                "status_no"=>$p->status_no,
                                'channel'=>$p->channel,
                                "datanya"=>$payment_detail,
                        );
                    }



                   
                      
                       
                        echo json_encode($this->show_data_object($data));


                

                     });
            
        } catch (Exception $e) {
            // return response()->json(["data"=>$request->all(), "log"=>$e->getMessage()]);
            return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }

    }


    function batal_payment(Request $request){

           try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

    
                $wkt = date("Y-m-d H:i:s") ;
                $id_payment = $request->id_payment;
                
                   
                       
                        $pay = DB::select('select status from payment where id_auth="'.$id_auth.'" and id="'.$id_payment.'"');

                        $pay_sts = json_decode($pay[0]->status);

                        foreach($pay_sts as $key => $s){

                            $nama = str_replace('menunggu_pembayaran','dibatalkan',$s->sts);

                             DB::update("UPDATE payment set 
                                 status_no = 99,
                                 tanggal_batal= '$wkt',  
                                 status = JSON_REPLACE(`status`, '$[$key].sts', '$nama') 
                                 where `id` = ".$id_payment);

                        }

                     echo json_encode($this->res_insert("sukses"));
                 
                    
                });
                    
        } catch (Exception $e) {
            return response()->json(["data"=>$request->all(), "log"=>$e->getMessage()]);
           // return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }

    }



    public function riwayat_pembayaran(Request $request){

     
      


              try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                error_reporting(0);

                       
                            //$payment = Payment::where('status','sukses_formulir')->where('id_auth',$id_auth)->where('status_no',1)->get();

                           

                            $payment = DB::select('select *  from payment where  JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_kuliah%" and status_no = 1 and id_auth="'.$id_auth.'"');


                            foreach($payment as $s){
                                 $payment_detail = DB::select('select (select m.nama_komponen from komponen_biaya as k join m_komponen as m on k.id_komponen = m.id_komponen  where k.id_komponen_biaya = payment_detail.id_komponen_biaya)as nama_komponen, payment_detail.* from payment_detail where id_payment = "'.$s->id.'"');


                                 $data[] = array(
                                         "id"=> $s->id,
                                        "id_kampus"=> $s->id_kampus,
                                        "id_auth"=> $s->id_auth,
                                        "nomor_va"=> $s->nomor_va,
                                        "tanggal_aktif"=> $s->tanggal_aktif,
                                        "tanggal_expire"=> $s->tanggal_expire,
                                        "tanggal_batal"=> $s->tanggal_batal,
                                        "status"=> $s->status,
                                        "created_at"=>$s->created_at,
                                        "total_tagihan"=> $s->total_tagihan,
                                        "status_bayar"=> $s->status_bayar,
                                        "tanggal_bayar"=> $s->tanggal_bayar,
                                        "no_invoice"=> $s->no_invoice,
                                        "status_no"=> $s->status_no,
                                        'channel'=>$s->channel,
                                         "datanya"=>$payment_detail,
                                    );
                            }

                            

                            


                            // echo "<pre>";
                            // echo count($data[0]['id_payment']);
                            // die;

                            if($payment){
                                $data2 = $data;
                            }else{
                                $data2 = array();
                            }
                       
                        echo json_encode($this->show_data_object($data2));


                

                     });
            
        } catch (Exception $e) {
             return response()->json(["data"=>$request->all(), "log"=>$e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }




    }


     function bayar_payment(Request $request){

           try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

               // die;

                 $mhs = DB::table('auth')
                ->join('mahasiswa', 'auth.id', '=', 'mahasiswa.id_auth')
                ->where('auth.id', $id_auth)
                ->select('mahasiswa.id')
                ->first();

                $id_mahasiswa = $mhs->id;

                 $wkt = date("Y-m-d H:i:s") ;

                $nomor_va = $request->nomor_va;
               

                  
                       $pay = DB::select('select status,id from payment where id_auth="'.$id_auth.'" and nomor_va="'.$nomor_va.'"');

                       $payment_detail = DB::select('select t.id_tagihan_mahasiswa from payment_detail as p join tagihan_mahasiswa as t on t.id_komponen_biaya = p.id_komponen_biaya 
                            where p.id_payment = "'.$pay[0]->id.'" and id_mahasiswa = "'.$id_mahasiswa.'"');

                     



                        $pay_sts = json_decode($pay[0]->status);

                         

                        foreach($pay_sts as $key => $s){

                            $nama = str_replace('menunggu_pembayaran','sukses',$s->sts);

                             DB::update("UPDATE payment set 
                                 status_no = 1,
                                 tanggal_bayar= '$wkt',  
                                 status = JSON_REPLACE(`status`, '$[$key].sts', '$nama') 
                                 where `nomor_va` = ".$nomor_va);


                        }

                        foreach($payment_detail as $p){

                            $data_update = array(
                                'status'=>1,
                                'tgl_bayar'=>date('Y-m-d H:i:s'),
                            );

                            DB::table('tagihan_mahasiswa')->where('id_tagihan_mahasiswa',$p->id_tagihan_mahasiswa)->update($data_update);
                        }
                   

                        

                    



                     echo json_encode($this->res_insert("sukses"));
                
                    
                });
                    
        } catch (Exception $e) {
             return response()->json(["data"=>$request->all(), "log"=>$e->getMessage()]);
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
