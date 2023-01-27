<?php

namespace App\Http\Controllers;

use App\Models\MasterKampus;
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

class PmbUserController extends Controller
{
    use utility;
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function tahapan(){
         
             // $id_auth        = 116;
             // $id_kampus      = 51;

       try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use($payload){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                  

             $gelombang = DB::table('pmb_gelombang')
                     ->where('tgl_mulai','<=',date('Y-m-d'))
                     ->where('tgl_selesai','>=',date('Y-m-d'))
                     ->where('id_kampus',$id_kampus)
                     ->first();

                $tahapan = DB::table('pmb_tahapan_pendaftaran')
                     ->where('id_auth',$id_auth)
                     ->first();





                     if($gelombang->ujian_seleksi==1){

                            $data_json = array(
                                 'id_pmb_tahapan_pendaftaran' => $tahapan->id_pmb_tahapan_pendaftaran,
                                 'id_auth' => $tahapan->id_auth,
                                 'formulir_pendaftaran' => $tahapan->formulir_pendaftaran,
                                 'info_pembayaran' => $tahapan->info_pembayaran,
                                 'lengkapi_berkas' => $tahapan->lengkapi_berkas,
                                 'jadwal_uji_seleksi' => $tahapan->jadwal_uji_seleksi,
                                 'info_kelulusan' => $tahapan->info_kelulusan,
                                 'id_gelombang'=>$gelombang->id_gelombang,
                                 'total'=>$tahapan->total,
                            );
                     }else{
                         $data_json = array(
                                 'id_pmb_tahapan_pendaftaran' => $tahapan->id_pmb_tahapan_pendaftaran,
                                 'id_auth' => $tahapan->id_auth,
                                 'formulir_pendaftaran' => $tahapan->formulir_pendaftaran,
                                 'info_pembayaran' => $tahapan->info_pembayaran,
                                 'lengkapi_berkas' => $tahapan->lengkapi_berkas,
                                // 'jadwal_uji_seleksi' => $tahapan->jadwal_uji_seleksi,
                                 'info_kelulusan' => $tahapan->info_kelulusan,
                                  'id_gelombang'=>$gelombang->id_gelombang,
                                  'total'=>$tahapan->total,
                            );
                     }


                     echo json_encode($this->show_data_object($data_json));

                       }); 

                   

                 } catch (Exception $e) {
            return response()->json([ "log"=>$e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    public function jadwal_uji_seleksi(){
         try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use($payload){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                   $mhs = DB::table('mahasiswa')
                  ->where('mahasiswa.id_auth', $id_auth)
                  ->select('id_gelombang','jurusan1','pmb_jadwal_ujian')
                  ->first();




                    $gelombang = DB::table('pmb_gelombang')
                     ->join('pmb_jadwal', 'pmb_jadwal.id_gelombang', '=', 'pmb_gelombang.id_gelombang')
                    ->where('pmb_gelombang.id_gelombang',$mhs->id_gelombang)
                     ->where('pmb_gelombang.id_kampus',$id_kampus)
                     ->select('pmb_gelombang.nama_gelombang','pmb_gelombang.id_gelombang','pmb_jadwal.jadwal_ujian','pmb_jadwal.tgl_pengumuman')
                     ->first();

                     if($mhs->pmb_jadwal_ujian){
                        $jadwal_ujian = $mhs->pmb_jadwal_ujian;
                     }else{
                        $jadwal_ujian = $gelombang->jadwal_ujian;
                     }

                     if($gelombang->tgl_pengumuman<=date('Y-m-d')){
                        $status_pengumuman =  1;
                     }else{
                        $status_pengumuman = 0;
                     }


                         $data_json[] = array(
                            'nama_gelombang'=>$gelombang->nama_gelombang,
                            'id_gelombang'=>$gelombang->id_gelombang,
                            'jadwal_ujian'=>$this->TanggalIndo($jadwal_ujian),
                            'jadwal_pengumuman'=>$this->TanggalIndo($gelombang->tgl_pengumuman),
                            'status_pengumuman'=>$status_pengumuman,
                         );
                 

                

                        echo json_encode($this->show_data_object($data_json));

                       }); 

                   

                 } catch (Exception $e) {
            return response()->json([ "log"=>$e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


    function simulasi_biaya_user(Request $request){
          try {

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');


            DB::transaction(function () use($payload,$request){
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;

                   $jurusan = $request->jurusan;
                   $gelombang = $request->gelombang;
                  //$jurusan = $request->jurusan;


                   $semester  = DB::select('select status from komponen_biaya 
                    where id_gelombang = "'.$gelombang.'" and kode_jurusan = "'.$jurusan.'" and id_kampus="'.$id_kampus.'" and id_komponen NOT IN (1) group by status');



                   // $komponen  = DB::select('select * from m_komponen');

                    $komponen_biaya_semester1  = DB::select('select k.nama_komponen,kb.id_komponen from komponen_biaya as kb
                            join m_komponen as k on k.id_komponen = kb.id_komponen
                            where kb.id_gelombang = "'.$gelombang.'" 
                            and kb.kode_jurusan = "'.$jurusan.'" 
                            and kb.id_kampus="'.$id_kampus.'" 
                            and kb.id_komponen NOT IN (1) 
                            and kb.status = "semester1" group by k.nama_komponen,kb.id_komponen order by kb.id_komponen asc');

                     foreach($komponen_biaya_semester1 as $k){
                            $data_biaya_semester1[$k->id_komponen] = $k->nama_komponen;
                           
                        }


                        $komponen_biaya  = DB::select('select k.nama_komponen,kb.* from komponen_biaya as kb
                            join m_komponen as k on k.id_komponen = kb.id_komponen
                            where kb.id_gelombang = "'.$gelombang.'" 
                            and kb.kode_jurusan = "'.$jurusan.'" 
                            and kb.id_kampus="'.$id_kampus.'" 
                            and kb.id_komponen NOT IN (1)');

                          foreach($komponen_biaya as $k){
                            $data_komponen_biaya[$k->status][$k->id_komponen] = $k->biaya;
                            $data_komponen_cicil[$k->status][$k->id_komponen] = $k->keterangan;
                           
                        }


                        error_reporting(0);
                    foreach($semester as $s){


                        $komponen_array = [];
                        $komponen_array_cicil = [];
                        foreach($data_biaya_semester1 as $key => $d ){
                            $komponen_array[$d] = $data_komponen_biaya[$s->status][$key] ? $data_komponen_biaya[$s->status][$key] : '-' ;

                           

                            //$komponen_array_cicil[$d] = $data_komponen_cicil[$s->status][$key];

                              if($data_komponen_cicil[$s->status][$key]=='bisa di cicil'){
                                $komponen_array_cicil[$d] = $d;
                            }

                            
                        }


                        


                        $data_smester[] = array(
                            //'header' => $komponen_biaya_semester1,

                            'semester'=>$s->status,
                            'keterangan'=>implode(",", $komponen_array_cicil),
                            'keterangan2'=>$komponen_array_cicil,
                            'komponen'=>$komponen_array,
                        );

                        $data_json = array(
                            'header'=>$komponen_biaya_semester1,
                            'body'=>$data_smester,

                        );

                    }





                        

                       //   echo "<pre>";
                       // print_r($data_smester);

                        
                         echo json_encode($this->show_data_object($data_json));
                      


                }); 

                   

                 } catch (Exception $e) {
            return response()->json([ "log"=>$e->getMessage()]);
            //return response()->json($this->res_insert("fatal"));
        } catch (TokenExpiredException $e) {
            return response()->json($this->res_insert("token_expired"));
        } catch (TokenInvalidException $e) {
            return response()->json($this->res_insert("token_invalid"));
        } catch (JWTException $e) {
            return response()->json($this->res_insert("token_absent"));
        }
    }


     function cek_lulus(Request $request){
         try {


            error_reporting(0);
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();
                $id_mahasiswa = $request->id_mahasiswa;
                $id_auth = $payload->id;
                $id_kampus = $payload->id_kampus;

                 $mhs = DB::table('mahasiswa')
                        ->join('auth', 'auth.id', '=', 'mahasiswa.id_auth')
                      ->where('mahasiswa.id_auth', $id_auth)
                       ->where('auth.id_kampus', $id_kampus)
                      ->select('mahasiswa.pmb_status')
                      ->first();

                      // echo "<pre>";
                      // print_r($mhs);
                      // die;

                    if($mhs->pmb_status=='lulus'){
                        $status = 1;
                        $messege = "Selamat Anda Lulus";
                    }elseif($mhs->pmb_status=='pindah_jurusan'){
                        $status = 2;
                        $messege = "Terimakasih Atas Konfirmasinya";
                    }


                    else{
                         $status = 0;
                        $messege = "Mohon Maaf Anda Belum Lulus ";
                    }

                $data_json = array(
                    'status'=>$status,
                    'messege'=>$messege,
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


    

   

}
