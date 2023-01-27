<?php

namespace App\Http\Controllers;

use App\Models\BerkasMahasiswa;
// use App\Models\mahasiswa;
use App\Traits\utility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class BerkasMahasiswaController extends Controller
{
    use utility;

    public function upload_berkas(Request $request)
    {
        try {
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();

            $mahasiswa = DB::table('mahasiswa')
                ->where('id_auth', $payload->id)
                ->first();

            // echo "<pre>";
            // print_r($payload->id);
            // die;

            DB::transaction(function () use ($request, $mahasiswa, $payload) {
                if ($request->has('foto')) {
                    $type_foto = $request->file('foto')->getClientMimeType();
                    $file_foto = str_replace(' ', '_', $mahasiswa->nama) . "_foto." . $request->file('foto')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $payload->id)->where('tipe_file','foto')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/foto/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/foto/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }

                    $simpan_foto = $request->file('foto')->move('public/berkas_mahasiswa/foto', $file_foto);
                    $berkas->id_auth    = $payload->id;
                    $berkas->nama_file  = str_replace(' ', '_', $file_foto);
                    $berkas->tipe_file  = 'foto';
                    $berkas->save();
                }
                if ($request->has('ktp')) {
                    $type_ktp = $request->file('ktp')->getClientMimeType();
                    $file_ktp = str_replace(' ', '_', $mahasiswa->nama) . "_ktp." . $request->file('ktp')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $payload->id)->where('tipe_file','ktp')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/ktp/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/ktp/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }
                    $simpan_ktp = $request->file('ktp')->move('public/berkas_mahasiswa/ktp', $file_ktp);
                    $berkas->id_auth    = $payload->id;
                    $berkas->nama_file  = str_replace(' ', '_', $file_ktp);
                    $berkas->tipe_file  = 'ktp';
                    $berkas->save();
                }
                if ($request->has('kk')) {
                    $type_kk = $request->file('kk')->getClientMimeType();
                    $file_kk = str_replace(' ', '_', $mahasiswa->nama) . "_kk." . $request->file('kk')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $payload->id)->where('tipe_file','kk')->first();
                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/kk/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/kk/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }
                    $simpan_kk = $request->file('kk')->move('public/berkas_mahasiswa/kk', $file_kk);
                    $berkas->id_auth    = $payload->id;
                    $berkas->nama_file  = str_replace(' ', '_', $file_kk);
                    $berkas->tipe_file  = 'kk';
                    $berkas->save();
                }
                if ($request->has('ijazah')) {
                    $type_ijazah = $request->file('ijazah')->getClientMimeType();
                    $file_ijazah = str_replace(' ', '_', $mahasiswa->nama) . "_ijazah." . $request->file('ijazah')->getClientOriginalExtension();

                    $berkas             = BerkasMahasiswa::where('id_auth', $payload->id)->where('tipe_file','ijazah')->first();

                    if ($berkas != null && file_exists(public_path('berkas_mahasiswa/ijazah/' . $berkas->nama_file))) {
                        unlink(public_path('berkas_mahasiswa/ijazah/' . $berkas->nama_file));
                    }
                    if ($berkas == null) {
                        $berkas = new BerkasMahasiswa();
                    }
                    $simpan_ijazah = $request->file('ijazah')->move('public/berkas_mahasiswa/ijazah', $file_ijazah);
                    $berkas->id_auth    = $payload->id;
                    $berkas->nama_file  = str_replace(' ', '_', $file_ijazah);
                    $berkas->tipe_file  = 'ijazah';
                    $berkas->save();

                    DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['lengkapi_berkas' => 1]);

                    $total = DB::select('select sum(formulir_pendaftaran+info_pembayaran+lengkapi_berkas+jadwal_uji_seleksi+info_kelulusan) as total from pmb_tahapan_pendaftaran  where id_auth = "' . $payload->id . '"');

                    DB::table('pmb_tahapan_pendaftaran')->where('id_auth', $payload->id)->update(['total' => $total[0]->total]);
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
            $payload    = JWTAuth::parseToken()->getPayload()->get('data');
            //  $mahasiswa  = mahasiswa::where('id_auth',$payload->id)->firstOrFail();

            $berkas = DB::table('berkas_mahasiswa')
                ->where('id_auth', $payload->id)
                ->get();

            foreach ($berkas as $b) {
                $ex = explode('_', $b->nama_file);
                $withoutExt = substr($ex[1], 0, (strrpos($ex[1], ".")));
                $detail_berkas[] = array(
                    'id_berkas' => $b->id,
                    'nama' => $b->nama_file,
                    'status' => $b->status,
                    'file' => asset('public/berkas_mahasiswa/' . $withoutExt . '/' . $b->nama_file),
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
}
