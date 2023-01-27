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

class PaymentController extends Controller
{
    use utility;


    function detail_Payment(Request $request){
          //$data_json = [];
          try {
            error_reporting(0);
              // $headers = apache_request_headers(); //get header
              // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                 $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                

                // $id_auth        = 116;
                // $id_kampus      = 51;


                  $mhs = DB::table('mahasiswa')
                  ->where('mahasiswa.id_auth', $id_auth)
                  ->select('id_gelombang','jurusan1')
                  ->first();


               
                 

                  
               if($request->tipe=='pmb'){


                $riwayat_payment = DB::select('select *  from payment where  (JSON_EXTRACT(`status`, "$[*].sts") like  "%menunggu_pembayaran_formulir%" or JSON_EXTRACT(`status`, "$[*].sts") like  "%menunggu_pembayaran_herreg%" )and status_no = 0 and id_auth="'.$id_auth.'"');

                             $komponen = DB::select("SELECT m.nama_komponen,k.biaya,k.id_komponen_biaya,k.keterangan,m.name_status as kelompok,
                                    (case when (select d.id_payment from payment_detail as d join payment as p on p.id = d.id_payment where p.status_no in (0,1)   and 
                                    p.id_auth = '".$id_auth."'
                                    and d.id_komponen_biaya = k.id_komponen_biaya limit 1) 
                                     THEN
                                          1 
                                     ELSE
                                          0 
                                     END) as status  from komponen_biaya as k 
                                join m_komponen as m on m.id_komponen = k.id_komponen
                                where k.id_kampus = '".$id_kampus."' and k.kode_jurusan = '".$mhs->jurusan1."' and k.id_gelombang = '".$mhs->id_gelombang."' 
                                and m.kelompok in ('formulir','pendaftaran') 
                                and m.id_kampus = '".$id_kampus."'
                                ");   





                             // echo "SELECT m.nama_komponen,k.biaya,k.id_komponen_biaya,k.keterangan,
                             //        (case when (select d.id_payment from payment_detail as d join payment as p on p.id = d.id_payment where p.status_no = 0 and 
                             //        p.id_auth = '".$id_auth."'
                             //        and d.id_komponen_biaya = k.id_komponen_biaya limit 1) 
                             //         THEN
                             //              1 
                             //         ELSE
                             //              0 
                             //         END) as status  from komponen_biaya as k 
                             //    join m_komponen as m on m.id_komponen = k.id_komponen
                             //    where k.id_kampus = '".$id_kampus."' and k.kode_jurusan = '".$mhs->jurusan1."' and k.id_gelombang = '".$mhs->id_gelombang."' 
                             //    and m.kategori = 'pmb'";






                                $num_status = 0;
                                foreach($komponen as $k){
                                    // if($k->status!=null)

                                    if($k->status!=null){
                                        $num_status += 1;
                                    }


                                }


                                error_reporting(0);
                                if($num_status==count($komponen) && $num_status!=0){
                                    $status_checkout = 1;
                                    $name_checkout = "sudah dibayar semua";
                                }elseif($riwayat_payment[0]){
                                     $status_checkout = 1;
                                    $name_checkout = "harus ada yg bayar dulu di checkout";
                                }

                                else{
                                    $status_checkout = 0;
                                    $name_checkout = "masih belum ada checkout";
                                }

                              //  echo $num_status;
                                //echo count($komponen);




                                $data_json = array(
                                    'status'=>$status_checkout,
                                    'messsage_checkout'=>$name_checkout,
                                    'datanya'=>$komponen,
                                );


               

               echo json_encode($this->show_data_object($data_json));
               }else{
                echo json_encode($this->res_insert("fatal"));
               }

              
               

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

    public function buat_payment(Request $request){


        // echo $this->buat_nomor();
        // die;

        try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                // $id_auth        = 116;
                // $id_kampus      = 51;

                  $mhs = DB::table('mahasiswa')
                  ->where('mahasiswa.id_auth', $id_auth)
                  ->select('id_gelombang','jurusan1')
                  ->first();


                  
                $komponen_biaya = $request->komponen_biaya;

                // echo "<pre>";
                // print_r($komponen_biaya);

                // die;


                $tanggal_aktif  = date('Y-m-d');
                $tanggal_expire = date('Y-m-d',strtotime("+1 day"));           
    
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
                foreach($request->komponen_biaya as $komponen){
                    $komponen = KomponenBiaya::where('id_komponen_biaya',$komponen)->firstOrFail();

                    $payment_detail                     = new PaymentDetail();
                    $payment_detail->id_payment         = $payment->id;
                    $payment_detail->id_komponen_biaya  = $komponen->id_komponen_biaya;


                    $payment_detail->biaya              = $komponen->biaya; 
                    $payment_detail->save();

                    $total_payment += $payment_detail->biaya;


                    // echo "<pre>";
                    // print_r($komponen);

                    $sts[] = array('sts'=>"menunggu_pembayaran_".$this->status_komponen($komponen->id_komponen));



                }




                    $data_total = array(
                        'total_tagihan'=>$total_payment,
                        'status'=>json_encode($sts)
                    );
                    DB::table('payment')->where('id',$payment->id)->update($data_total);



            });

            return response()->json($this->res_insert("sukses"));
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

                    $payment = DB::select('select *  from payment where  (JSON_EXTRACT(`status`, "$[*].sts") like  "%menunggu_pembayaran_formulir%" or JSON_EXTRACT(`status`, "$[*].sts") like  "%menunggu_pembayaran_herreg%" )and status_no = 0 and id_auth="'.$id_auth.'"');





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



      public function riwayat_pembayaran(Request $request){

     
      


              try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                error_reporting(0);

                       
                            //$payment = Payment::where('status','sukses_formulir')->where('id_auth',$id_auth)->where('status_no',1)->get();

                            $payment = DB::select('select *  from payment where  (JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_formulir%" or JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%" )and status_no = 1 and id_auth = "'.$id_auth.'"');


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

    function batal_payment(Request $request){

           try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

    
                $wkt = date("Y-m-d H:i:s") ;
                $id_payment = $request->id_payment;
                $tipe = $request->tipe;

                    if($tipe=='pmb'){
                       
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
                 }else{
                     echo json_encode($this->res_insert("fatal"));
                 }
                    
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


    function bayar_payment(Request $request){

           try {
            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

               // die;

                 $wkt = date("Y-m-d H:i:s") ;

                $nomor_va = $request->nomor_va;
                $tipe = $request->tipe;

                    if($tipe=='pmb'){
                       $pay = DB::select('select status from payment where id_auth="'.$id_auth.'" and nomor_va="'.$nomor_va.'"');

                        $pay_sts = json_decode($pay[0]->status);

                        foreach($pay_sts as $key => $s){

                            $nama = str_replace('menunggu_pembayaran','sukses',$s->sts);

                             DB::update("UPDATE payment set 
                                 status_no = 1,
                                 tanggal_bayar= '$wkt',  
                                 status = JSON_REPLACE(`status`, '$[$key].sts', '$nama') 
                                 where `nomor_va` = ".$nomor_va);


                        }
                   

                        $hasil = DB::select('select * from payment where JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%"  and id_auth = "'.$id_auth.'"');

                        if($hasil){
                                DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['info_pembayaran' => 1]);
                                $total = DB::select('select sum(formulir_pendaftaran+info_pembayaran+lengkapi_berkas+jadwal_uji_seleksi+info_kelulusan) as total from pmb_tahapan_pendaftaran  where id_auth = "' . $payload->id . '"');
                                DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['total' => $total[0]->total]);
                        }

                    



                     echo json_encode($this->res_insert("sukses"));
                 }else{
                     echo json_encode($this->res_insert("fatal"));
                 }
                    
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


      function invoice_admin(Request $request){

           
            
              try {
                $payload            = JWTAuth::parseToken()->getPayload()->get('data');
                DB::transaction(function () use($payload,$request){

                    error_reporting(0);
                $id_auth        = $request->id_auth;
                $id_kampus      = $payload->id_kampus;
                 $id_payment = $request->id_payment;
                 

                 $mhs = DB::table('mahasiswa')
                  ->where('mahasiswa.id_auth', $id_auth)
                  ->select('id_gelombang','jurusan1')
                  ->first();


                  


                // $invoice = DB::table('payment')
                //      ->join('mahasiswa', 'mahasiswa.id_auth', '=', 'payment.id_auth')
                //      ->where('payment.id_auth', $id_auth)
                //      ->where('payment.id_kampus', $id_kampus)
                //      ->where('payment.id', $id_payment)
                //      ->where('payment.status', 'sukses_formulir')
                //      ->select('mahasiswa.nama','payment.no_invoice','payment.nomor_va','payment.total_tagihan')
                //      ->first();

                  $invoice = DB::select('select no_invoice,nomor_va,total_tagihan,(select nama from mahasiswa where id_auth = "'.$id_auth.'" ) as nama from payment where id_auth = "'.$id_auth.'" and status_no = 1 and id_kampus = "'.$id_kampus.'" and id = "'.$id_payment.'" 
                         and  (JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_formulir%" or JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%" ) ');

                 
                        $komponen = DB::table('payment_detail')
                     ->join('payment', 'payment_detail.id_payment', '=', 'payment.id')
                     ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'payment_detail.id_komponen_biaya')
                     ->join('m_komponen', 'm_komponen.id_komponen', '=', 'komponen_biaya.id_komponen')
                     ->where('komponen_biaya.id_kampus', $id_kampus)
                     ->where('payment.id_auth', $id_auth)
                     ->where('payment.id', $id_payment)
                   
                    ->select('m_komponen.nama_komponen','komponen_biaya.biaya','komponen_biaya.id_komponen_biaya')
                    ->get();

                    
               

                    $data_object = array(
                        'data'=>$invoice[0],
                        'listdata'=>$komponen,
                    );

                   echo json_encode($this->show_data_object($data_object));

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






    function invoice(Request $request){

           
            
              try {
                $payload            = JWTAuth::parseToken()->getPayload()->get('data');
                DB::transaction(function () use($payload,$request){

                    error_reporting(0);
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;
                 $id_payment = $request->id_payment;

                 $mhs = DB::table('mahasiswa')
                  ->where('mahasiswa.id_auth', $id_auth)
                  ->select('id_gelombang','jurusan1')
                  ->first();


                  


                // $invoice = DB::table('payment')
                //      ->join('mahasiswa', 'mahasiswa.id_auth', '=', 'payment.id_auth')
                //      ->where('payment.id_auth', $id_auth)
                //      ->where('payment.id_kampus', $id_kampus)
                //      ->where('payment.id', $id_payment)
                //      ->where('payment.status', 'sukses_formulir')
                //      ->select('mahasiswa.nama','payment.no_invoice','payment.nomor_va','payment.total_tagihan')
                //      ->first();

                  $invoice = DB::select('select no_invoice,nomor_va,total_tagihan,(select nama from mahasiswa where id_auth = "'.$id_auth.'" ) as nama from payment where id_auth = "'.$id_auth.'" and status_no = 1 and id_kampus = "'.$id_kampus.'" and id = "'.$id_payment.'" 
                         and  (JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_formulir%" or JSON_EXTRACT(`status`, "$[*].sts") like  "%sukses_herreg%" ) ');

                 
                        $komponen = DB::table('payment_detail')
                     ->join('payment', 'payment_detail.id_payment', '=', 'payment.id')
                     ->join('komponen_biaya', 'komponen_biaya.id_komponen_biaya', '=', 'payment_detail.id_komponen_biaya')
                     ->join('m_komponen', 'm_komponen.id_komponen', '=', 'komponen_biaya.id_komponen')
                     ->where('komponen_biaya.id_kampus', $id_kampus)
                     ->where('payment.id_auth', $id_auth)
                     ->where('payment.id', $id_payment)
                   
                    ->select('m_komponen.nama_komponen','komponen_biaya.biaya','komponen_biaya.id_komponen_biaya')
                    ->get();

                    
               

                    $data_object = array(
                        'data'=>$invoice[0],
                        'listdata'=>$komponen,
                    );

                   echo json_encode($this->show_data_object($data_object));

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


     function metode_pembayaran(Request $request){

        error_reporting(0);
        //https://edunitas.com/assets/edu-img/channel/bca.jpg

        //$keycode = ;

          try {
                $payload            = JWTAuth::parseToken()->getPayload()->get('data')

                $id_auth = $payload->id;
                $id_kampus = $payload->id_kampus;

        $novirtual_keycode = $request->nomor_va;
        $channel = $request->channel;
 

        $novirtual = $novirtual_keycode;
        $howtopay = array();
        $howtopaych = array();

        //  $channel =  DB::table('payment')
        //  ->where('nomor_va','=',$novirtual)
        // ->select('channel')
        //  ->first();


           $enitas_howtopay =  DB::table('enitas_howtopay')
         ->where('jenisrek','=','va')
         ->where('jenistagihan','=','all')
         ->get()
         ->toArray();
       

        

        

       

      // $enitas_howtopay2 = json_decode($enitas_howtopay, true);

         // echo "<pre>";
         // print_r($enitas_howtopay);
         // echo "</pre>";
         // die;

         foreach ($enitas_howtopay as $k => $v) {
                $howtopay[$v->metode] = $v->cara;
                $howtopaych[$v->metode] = $v->pelayanan;
            }


         $campus =  DB::table('kampus')
         ->where('id','=',$id_kampus)
         ->get()
         ->toArray();

         // echo "<pre>";
         // print_r($campus);
         // die;

         // echo "<pre>";
   


    foreach ($campus as $k => $v) {

    $cpay = @explode(',', $v->channel);



    // $channelcode = array('bni' => '11', 'bsm' => '9009026','bsi' => '9009026', 'cimbniaga' => '4459', 'indomaret' => '', 'alfamart' => array('faspay' => '329200'), 'ewallet' => '', 'bni-edu' => '98893383');

    $channelcode = array('bni' => '11', 'bsm' => '9009026','bsi' => '9009026', 'cimbniaga' => '4459', 'indomaret' => '', 'alfamart' => array('faspay' => '329200', 'doku' => ''), 'ewallet' => '', 'bni-edu' => '98893383','shopee' => '','nicepay' => '');

    @reset($channelcode);
    foreach ($channelcode as $kpay => $vpay) {

        // echo "<pre>";
        // print_r($kpay);
        // die;

        @reset($cpay);
        if (in_array($kpay, $cpay)) {

            if (is_array($vpay)) {

                foreach ($vpay as $kvpay => $vvpay) {

                    // $invoiceparam['novirtual_'.$kvpay] = $vvpay . $invoiceparam['novirtual'];;
                    @reset($howtopay);
                    @reset($howtopaych);
                    $result[$kvpay]->nopembayaran = $vvpay . $novirtual;

                     $result[$kvpay]->metode = $kvpay;

                    // $result[$kvpay]->gambar = "https://edunitas.com/assets/edu-img/channel/". $kvpay .".png";

                       $result[$kvpay]->gambar = public_path('channel/'.$kvpay.".png");

                    

                    if (isset($howtopaych[$kvpay])) {
                        $result[$kvpay]->pelayanan = $howtopaych[$kvpay];
                    }
                    if (isset($howtopay[$kvpay])) {
                        $result[$kvpay]->carapembayaran = $howtopay[$kvpay];
                    }
                }
            } else {

                $keypay = '';
                $kodepay = '';
                if ($kpay == 'bni' && strlen(trim($v->client_bni))) {

                    $keypay = 'novirtual_bni';
                    $kodepay = '8' . $v->client_bni . $vpay . $novirtual;
                } elseif ($kpay == 'cimbniaga') {

                    $keypay = 'novirtual_niaga';
                    $kodepay = $vpay . $novirtual;
                } elseif ($kpay == 'bsm') {

                    $keypay = 'novirtual_bsm';
                    $kodepay = $vpay . $novirtual;
                } elseif ($kpay == 'bsi') {

                    $keypay = 'novirtual_bsi';
                    $kodepay = $vpay . $novirtual;
                } elseif ($kpay == 'alfamart') {

                    $keypay = 'novirtual_alfamart';
                    $kodepay = $vpay . $novirtual;
                } elseif ($kpay == 'indomaret') {

                    $keypay = 'novirtual_mst';
                    $kodepay = $vpay . $novirtual;
                } elseif ($kpay == 'ewallet') {

                    $keypay = 'novirtual_ewallet';
                    $kodepay = $vpay . $novirtual;
                } elseif ($kpay == 'bni-edu') {

                    $kpay = 'bni';
                    $keypay = 'novirtual_bni';
                    $kodepay = $vpay . $novirtual;
                }
                elseif ($kpay == 'shopee') {

                    $kpay = 'shopee';
                    $keypay = 'novirtual_shopee';
                    $kodepay = $vpay . $novirtual;
                }

                @reset($howtopay);
                @reset($howtopaych);
                $result[$kpay]->nopembayaran = $kodepay;

                 $result[$kpay]->metode = $kpay;
                                         //https://edunitas.com/assets/edu-img/channel/
               // $result[$kpay]->gambar = "https://edunitas.com/assets/edu-img/channel/". $kpay .".jpg";

                 $result[$kpay]->gambar = asset('public/channel/'.$kpay.".png");


                if (isset($howtopaych[$kpay])) {
                    $result[$kpay]->pelayanan = $howtopaych[$kpay];
                }
                if (isset($howtopay[$kpay])) {
                    $result[$kpay]->carapembayaran = $howtopay[$kpay];
                }

            }

        }

    }



    }

   
        if($channel){
               $result2[] = $result[$channel];
        
         }else{
              $result2 =   $result;
         }


    //       echo "<pre>";
    // print_r($result2);
    // die;




    foreach($result2 as $m){

         $pem = str_replace(array("_nopembayaran_","_nova_"), $m->nopembayaran, $m->carapembayaran);

            if($m->metode=="doku" || $m->metode=="faspay"){
                $nama = "Alfamart  via  ".$m->metode;

            }else{
                $nama = $m->metode;
            }
                $output_json[] = array(
                    'id'            => 1,
                    'logo'          =>$m->gambar,
                    'metode'        => $nama,
                    'kelompok'      =>$m->metode,
                    'cara'          => $pem,
                    'pelayanan'     => $m->pelayanan,
                    'nopembayaran'  => $m->nopembayaran,
                    'jenisPayment'  => "tes",
                    'jenisrek'      => "tes",
                    'urutan'        => "tes",
                    'no_pembayaran_pev'=>$m->nopembayaran,

                );
    }

    // return $result;
    // die;
    if($output_json) {
            return response()->json($this->show_data_object($output_json));
        }else{

            $c = array(
            "id"=> 1,
            "metode"=> "bni",
            "kelompok"=> "bni",
            "cara"=> "<p><strong>SETORAN&nbsp;</strong><strong>TUNAI&nbsp;</strong><strong>(BNI dan&nbsp;</strong><strong>",
            "pelayanan"=> 2000,
            "nopembayaran"=> "*|NOVIRTUAL_BNI|*",
            "jenisPayment"=> "all",
            "jenisrek"=> "va",
            "urutan"=> null
            );
            //echo json_encode($c);
            echo json_encode($this->show_data_object($c));
        }


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
