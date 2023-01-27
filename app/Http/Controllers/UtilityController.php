<?php

namespace App\Http\Controllers;

use App\Models\KomponenBiaya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\utility;
use Exception;

class UtilityController extends Controller
{
    use utility;

    public function get_data($type,Request $request){

        // echo "tes";

        $data = array('tes'=>'tes');
        // echo json_encode($data);
        // die;

        if($type=="m_komponen"){
            return response()->json(
                $this->show_data_object(
                   $m_komponen =  DB::table('m_komponen')->get()
                )
            );
        }

        //  if($type=="kelas"){
        //     // $conf_kumkod =  DB::table('conf_kumkod')->where('jenis','kelas')->where('kelompok','pt')->where('status','1')->get();
        //     // echo json_encode($conf_kumkod);
        //     // die;
        //     return response()->json(
        //         $this->show_data_object(
        //            $conf_kumkod =  DB::table('conf_kumkod')->where('jenis','kelas')->where('kelompok','pt')->where('status','1')->get()
        //         )
        //     );
        // }


        
          if($type=="provinsi"){
            return response()->json(
                $this->show_data_object(
                   $provinsi =  DB::table('m_wilayah')->where('parent','=','000000')->get()
                )
            );
        }

        if($type=="area"){
              $id_parent = $request->get('id_parent');
                    $kecamatan =  DB::table('m_wilayah')->where('parent','=',$id_parent)->get();
                 return response()->json(
                $this->show_data_object($kecamatan)
            );
        }

        if($type=="agama"){
                  
                   
                 return response()->json(
                $this->show_data_object(
                  DB::table('m_agama') ->get())
            );
        }

        if($type=="jenis_tinggal"){
                  
                   
                 return response()->json(
                $this->show_data_object(
                  DB::table('m_jenis_tinggal') ->get())
            );
        }


        if($type=="jenis_pendaftaran"){
                  
                   
                 return response()->json(
                $this->show_data_object(
                  DB::table('m_jenis_pendaftaran')->where('status',1)->orderBy('no_urut','asc')->get())
            );
        }

         if($type=="negara"){
                  
                   
                 return response()->json(
                $this->show_data_object(
                  DB::table('m_negara') ->get())
            );
        }

         if($type=="event"){
                  
                   
                 return response()->json(
                $this->show_data_object(
                  DB::table('m_event') ->get())
            );
        }

         if($type=="jenis_pembayaran"){
                  
                   
                 return response()->json(
                $this->show_data_object(
                  DB::table('m_jenis_pembayaran')->where('status',1)->get())
            );
        }


        if($type=="transport"){
            return response()->json(
                $this->show_data_object(
                    DB::table('m_transportasi')
                    // ->select('id_alat_transport as value','nm_alat_transport as label')
                    ->get()
                )
            );
        }
        else if($type=="pendidikan"){
            return response()->json(
                $this->show_data_object(
                    DB::table('m_jenjang_pendidikan')
                    // ->select('id_jenj_didik as value','nm_jenj_didik as label')
                    ->get()
                )
            );
        }
        else if($type=="pekerjaan"){
            return response()->json(
                $this->show_data_object(
                    DB::table('m_pekerjaan')
                    
                    ->get()
                )
            );
        }
        else if($type=="penghasilan"){
            return response()->json(
                $this->show_data_object(
                    DB::table('m_penghasilan')
                    
                    ->get()
                )
            );
        }

        else if($type=="jalur_masuk"){
            return response()->json(
                $this->show_data_object(
                    DB::table('m_jalur_masuk')
                    
                    ->get()
                )
            );
        }

         else if($type=="peraturan"){
            return response()->json(
                $this->show_data_object(
                    DB::table('m_peraturan')
                    
                    ->get()
                )
            );
        }



        else if($type=="kelompok_komponen"){
           // $kelompok = DB::table('m_kelompok_komponen')->get();
            return response()->json(
                $this->show_data_object(DB::table('m_kelompok_komponen')->where('status',1)->get())
            );
        }

         else if($type=="kelompok_jenjang"){
           // $kelompok = DB::table('m_kelompok_jenjang')->get();
            return response()->json(
                $this->show_data_object(DB::table('m_kelompok_jenjang')->where('status',1)->orderBy('nourut','ASC')->get())
            );
        }

         else if($type=="leveledu"){
           // $kelompok = DB::table('m_kelompok_jenjang')->get();
            return response()->json(
                $this->show_data_object(DB::table('m_leveledu')->whereIn('kelompok',['sma','pt'])->orderBy('urutan','ASC')->get())
            );
        }


        

      


        else if($type=="jurusan"){
            $datas = DB::table('m_jurusan');
            // ->select('kode as value', DB::raw("concat(nama,' - ',jenjang) as label"));
            if($request->has('jenjang')) $datas->where('jenjang',$request->get('jenjang'));
            $datas = $datas->groupBy('kode','id','nama','jenjang','gelar','karir','komp','youtube','kelompok','lastupdate','rekomendasi','status','crdt','mddt')->get();
            
            return response()->json(
                $this->show_data_object($datas)
            );
        }
        else if($type=="komponen_biaya"){
            try {
                $kampus = $this->ambil_id_kampus();
                $datas = KomponenBiaya::with([
                    'master_komponen',
                    'jurusan',
                    'tahun_ajar'=>function($query){
                        $query->select('tahun_ajar')->where('status',1);
                    }
                ])
                ->where('id_kampus',$kampus->id)->get();
                
                $datas->each(function($row){
                    $row->value = $row->id_komponen_biaya;
                    $row->label = $row->master_komponen->nama_komponen;
                    unset(
                        $row->id_komponen_biaya,
                        $row->id_komponen,
                        $row->id_kampus,
                        $row->id_tahun_ajar,
                        $row->id_jurusan,
                        $row->jurusan,
                        $row->tahun_ajar,
                        $row->master_komponen
                    );
                });

                return response()->json(
                    $this->show_data_object($datas)
                );
            } catch (Exception $e) {
                return response()->json($this->res_insert("fatal"));
            }
        }
        else{
            return response()->json($this->res_insert("fatal"));
        }
    }


    function cek_query(Request $req){

        $kode_jurusan = $req->kode_jurusan;

                 $tes = DB::table('tagihan_mahasiswa');
             
                 if($kode_jurusan) {
                    $tes = $tes->where('kode_jurusan', $kode_jurusan)->get();
                } else {
                    $tes = $tes->get();
                }




            echo json_encode($tes);
    }




   
}
