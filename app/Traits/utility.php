<?php

namespace App\Traits;

use App\Models\MasterKampus;
use App\Models\Payment;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;



trait utility
{
    public function ambil_subdomain()
    {
        $url = parse_url($_SERVER['HTTP_ORIGIN']);
        return $url['host'];
    }
    public function ambil_id_kampus()
    {
        $url = parse_url($_SERVER['HTTP_ORIGIN']);
        $subdomain = MasterKampus::select('id', 'nama_kampus', 'subdomain', 'kode_kampus')->where('subdomain', $url['host'])->first();

        return $subdomain;
    }
    public function no_invoice($list_nomor_terdaftar = [])
    {
        $nomor = sprintf("%8d", mt_rand(1, 9999999999));
        if (in_array($nomor, $list_nomor_terdaftar)) {
            $this->buat_nomor($list_nomor_terdaftar);
        } else {
            return $nomor;
        }
    }

    public function kampus($id)
    {

        error_reporting(0);
        $kampus = DB::table('kampus')
            ->where('id', $id)
            ->first();

        // if($komponen->nama_komponen){
        //     $komponen_nama = $komponen->nama_komponen;
        // }else{
        //     $komponen_nama = null;
        // }

        return $kampus;
    }



    public function kelompok_jenjang($id)
    {

        error_reporting(0);
        $komponen = DB::table('m_kelompok_jenjang')
            ->where('id_kelompok_jenjang', $id)
            ->first();

        if ($komponen->nama_kelompok_jenjang) {
            $komponen_nama = $komponen->nama_kelompok_jenjang;
        } else {
            $komponen_nama = null;
        }

        return $komponen_nama;
    }

    public function get_kelompok_jenjang($id)
    {

        error_reporting(0);
        $komponen = DB::table('m_kelompok_jenjang')
            ->where('id_kelompok_jenjang', $id)
            ->first();



        return $komponen;
    }


    public function nama_komponen($id)
    {

        error_reporting(0);
        $komponen = DB::table('m_komponen')
            ->where('id_komponen', $id)
            ->first();

        if ($komponen->nama_komponen) {
            $komponen_nama = $komponen->nama_komponen;
        } else {
            $komponen_nama = null;
        }

        return $komponen_nama;
    }


    public function nama_prodi($kode)
    {

        error_reporting(0);
        $jurusan = DB::table('m_jurusan')
            ->where('kode', $kode)
            ->first();

        if ($jurusan->nama) {
            $jurusan_nama = $jurusan->jenjang . ' - ' . $jurusan->nama;
        } else {
            $jurusan_nama = null;
        }

        return $jurusan_nama;
    }
    public function just_nama_prodi($kode)
    {

        error_reporting(0);
        $jurusan = DB::table('m_jurusan')
            ->where('kode', $kode)
            ->first();

        if ($jurusan->nama) {
            $jurusan_nama = $jurusan->nama;
        } else {
            $jurusan_nama = null;
        }

        return $jurusan_nama;
    }

    public function get_prodi($kode)
    {

        error_reporting(0);
        $jurusan = DB::table('m_jurusan')
            ->where('kode', $kode)
            ->first();


        return $jurusan;
    }


    public function status_komponen($id)
    {

        error_reporting(0);
        $komponen = DB::table('m_komponen')
            ->where('id_komponen', $id)
            ->first();

        if ($komponen->name_status) {
            $komponen_nama = $komponen->name_status;
        } else {
            $komponen_nama = null;
        }

        return $komponen_nama;
    }

    public function cek_gelombang($id_kampus)
    {

        $gelombang = DB::table('pmb_gelombang')
            ->where('tgl_mulai', '<=', date('Y-m-d'))
            ->where('tgl_selesai', '>=', date('Y-m-d'))
            ->where('id_kampus', $id_kampus)
            ->first();

        return $gelombang;
    }


    public function cek_tahun_ajar($id_gelombang)
    {

        $tahun_ajar = DB::table('pmb_gelombang')
            ->join('tahun_ajar', 'tahun_ajar.id', '=', 'pmb_gelombang.id_tahun_ajar')
            ->where('pmb_gelombang.id_gelombang', $id_gelombang)
            ->first();

        return $tahun_ajar;
    }


    public function tahun_ajar($id)
    {

        $tahun_ajar = DB::table('tahun_ajar')

            ->where('id', $id)
            ->first();

        return $tahun_ajar;
    }

    public function nama_tahun_ajar($id)
    {

        $tahun_ajar2 = DB::table('tahun_ajar')

            ->where('id', $id)
            ->first();

        return $tahun_ajar2->tahun_ajar;
    }




    public function agama($id)
    {
        error_reporting(0);
        $agama = DB::table('m_agama')

            ->where('id_agama', $id)
            ->first();

        if ($agama->nm_agama) {
            $agama_nama = $agama->nm_agama;
        } else {
            $agama_nama = null;
        }

        return $agama_nama;
    }


    public function provinsi($id)
    {
        error_reporting(0);
        $provinsi =  DB::table('m_wilayah')->where('kode', '=', $id)->first();

        if ($provinsi->nama) {
            $provinsi_nama = $provinsi->nama;
        } else {
            $provinsi_nama = null;
        }

        return $provinsi_nama;
    }


    public function transportasi($id)
    {
        error_reporting(0);
        $transportasi =  DB::table('m_transportasi')->where('id_alat_transport', '=', $id)->first();

        if ($transportasi->nm_alat_transport) {
            $transportasi_nama = $transportasi->nm_alat_transport;
        } else {
            $transportasi_nama = null;
        }

        return $transportasi_nama;
    }

    public function peraturan($id)
    {
        error_reporting(0);
        $transportasi =  DB::table('m_peraturan')->where('id_peraturan', '=', $id)->first();

        if ($transportasi->nama) {
            $transportasi_nama = $transportasi->nama;
        } else {
            $transportasi_nama = null;
        }

        return $transportasi_nama;
    }


    public function jenis_pendaftaran($id)
    {
        error_reporting(0);
        $jalur_masuk =  DB::table('m_jenis_pendaftaran')->where('id_jns_daftar', '=', $id)->first();

        if ($jalur_masuk->nm_jns_daftar) {
            $jalur_masuk_nama = $jalur_masuk->nm_jns_daftar;
        } else {
            $jalur_masuk_nama = null;
        }

        return $jalur_masuk_nama;
    }

    public function kelas($id_kampus, $id)
    {
        error_reporting(0);
        $kelas =  DB::table('conf_kumkod')->where('id_kampus', '=', $id_kampus)->where('id', $id)->first();

        if ($kelas->nama) {
            $kelas_nama = $kelas->nama;
        } else {
            $kelas_nama = null;
        }

        return $kelas_nama;
    }








    public  function buat_nomor()
    {
        $default = "0000001";
        // $prefix = date('Ymd');
        $prefix = "5";
        // $sql = "SELECT CAST(MAX(SUBSTR(nomor_va," . (strlen($prefix) + 1) . "," . (strlen($default)) . ")) AS integer) nomaksimal
        //         FROM tagihan
        //         WHERE nomor_va LIKE ('" . $prefix . "%')";
        // $noberkas = Yii::app()->db->createCommand($sql)->queryRow();

        $noberkas = Payment::select('nomor_va')->orderby('nomor_va', 'desc')->first();

        if ($noberkas) {
            $no_baru =  (isset($noberkas['nomor_va']) ? (str_pad($noberkas['nomor_va'] + 1, strlen($default), 0, STR_PAD_LEFT)) : $default);
        } else {
            $no_baru = $prefix . (isset($noberkas['nomor_va']) ? (str_pad($noberkas['nomor_va'] + 1, strlen($default), 0, STR_PAD_LEFT)) : $default);
        }

        return $no_baru;
    }


    public  function buat_nomor_pmb_pendaftaran($id_kampus, $kode_jurusan)
    {
        error_reporting(0);
        $default = "000001";
        $prefix = date('Ym') . $kode_jurusan;
        // $prefix = "2";
        // $sql = "SELECT CAST(MAX(SUBSTR(nomor_va," . (strlen($prefix) + 1) . "," . (strlen($default)) . ")) AS integer) nomaksimal
        //         FROM tagihan
        //         WHERE nomor_va LIKE ('" . $prefix . "%')";
        // $noberkas = Yii::app()->db->createCommand($sql)->queryRow();

        //$noberkas = mahasiswa::select('pmb_no_pendaftaran')->where('id_kampus',$id_kampus)->orderby('pmb_no_pendaftaran','desc')->first();


        $noberkas = DB::table('mahasiswa')
            ->join('auth', 'mahasiswa.id_auth', '=', 'auth.id')
            ->where('auth.id_kampus', $id_kampus)
            ->where('mahasiswa.pmb_no_pendaftaran', 'LIKE', "%" . $prefix . "%")

            ->select('mahasiswa.pmb_no_pendaftaran')
            ->first();



        $no_formulir = $noberkas->pmb_no_pendaftaran;



        if ($no_formulir) {

            $no_urut = substr($no_formulir, 10, 6);
            $no_urut2 =  (isset($no_urut) ? (str_pad($no_urut + 1, strlen($default), 0, STR_PAD_LEFT)) : $default);
            $no_baru = $prefix . $no_urut2;
        } else {
            $no_baru = $prefix . (isset($no_formulir) ? (str_pad($no_formulir + 1, strlen($default), 0, STR_PAD_LEFT)) : $default);
        }

        return $no_baru;
    }



    public function null_data()
    {
        return [
            "kode" => "002",
            "message" => "gagal",
            "pageprop" => [
                "dari" => 0,
                "hingga" => 0,
                "totalData" => 0,
                "totalHalaman" => 0
            ],
            "listdata" => []
        ];
    }
    public function null_data_v2()
    {
        return [
            "kode" => "002",
            "message" => "gagal",
            "listdata" => [],
        ];
    }

    public function show_data($dari = 0, $hingga = 0, $totaldata = 0, $totalhalaman = 0, $data = null)
    {

        if ($data == null) {
            $data1 = [];
        } else {
            $data1 = $data;
        }

        return [
            "kode" => "001",
            "message" => "sukses",
            "pageprop" => [
                "dari" => $dari,
                "hingga" => $hingga,
                "totalData" => $totaldata,
                "totalHalaman" => $totalhalaman
            ],
            "listdata" => [
                $data1
            ]
        ];
    }

    public function show_data_object($data = null)
    {


        if ($data == null) {
            $data1 = [];
        } else {
            $data1 = $data;
        }


        return [
            "kode" => "001",
            "message" => "sukses",
            "listdata" => $data1,
        ];
    }


    public function show_data_object_pagination($dari = 0, $hingga = 0, $totaldata = 0, $totalhalaman = 0, $data = null)
    {

        if ($data == null) {
            $data1 = [];
        } else {
            $data1 = $data;
        }


        return [
            "kode" => "001",
            "message" => "sukses",
            "pageprop" => [
                "dari" => $dari,
                "hingga" => $hingga,
                "totalData" => $totaldata,
                "totalHalaman" => $totalhalaman
            ],



            "listdata" => $data1,
        ];
    }

    public function res_insert($status = false)
    {
        switch ($status) {
            case 'sukses':
                return [
                    "kode" => "001",
                    "message" => "berhasil"
                ];
            case 'gagal':
                return [
                    "kode" => "002",
                    "message" => "cannot insert"
                ];
            case 'invalid':
                return [
                    "kode" => "003",
                    "message" => "invalid value"
                ];
            case 'required':
                return [
                    "kode" => "004",
                    "message" => "field is required"
                ];
            case 'unique':
                return [
                    "kode" => "005",
                    "message" => "field is unique"
                ];
            case 'token_expired':
                return [
                    "kode" => "006",
                    "message" => "token is expired"
                ];
            case 'token_invalid':
                return [
                    "kode" => "007",
                    "message" => "token is invalid"
                ];
            case 'token_absent':
                return [
                    "kode" => "008",
                    "message" => "token not found"
                ];
            default:
                return [
                    "kode" => "999",
                    "message" => "unknown eror"
                ];
        }
    }


    function TanggalIndo($date)
    {
        if ($date == "") {
            return null;
        }

        if ($date == "0000-00-00") {
            return null;
        }

        $BulanIndo = array("Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");

        $tahun = substr($date, 0, 4);
        $bulan = substr($date, 5, 2);
        $tgl   = substr($date, 8, 2);

        $result = $tgl . " " . $BulanIndo[(int)$bulan - 1] . " " . $tahun;
        return ($result);
    }

    function BulanIndo($bln)
    {
        $bulan = $bln;
        switch ($bulan) {
            case 1:
                $bulan = "Januari";
                break;
            case 2:
                $bulan = "Februari";
                break;
            case 3:
                $bulan = "Maret";
                break;
            case 4:
                $bulan = "April";
                break;
            case 5:
                $bulan = "Mei";
                break;
            case 6:
                $bulan = "Juni";
                break;
            case 7:
                $bulan = "Juli";
                break;
            case 8:
                $bulan = "Agustus";
                break;
            case 9:
                $bulan = "September";
                break;
            case 10:
                $bulan = "Oktober";
                break;
            case 11:
                $bulan = "November";
                break;
            case 12:
                $bulan = "Desember";
                break;
        }
        return $bulan;
    }

    function DayIndonesia($tanggal)
    {
        $day = date('N', $tanggal);
        $dayList = array(
            '7' => 'Minggu',
            '1' => 'Senin',
            '2' => 'Selasa',
            '3' => 'Rabu',
            '4' => 'Kamis',
            '5' => 'Jumat',
            '6' => 'Sabtu'
        );
        return $dayList[$day];
    }

    // function getRangeDate($bulan,$tahun)
    // {
    //  $list=array();
    //  for($d=1; $d<=31; $d++)
    //  {
    //      $time=mktime(12, 0, 0, $bulan, $d, $tahun);          
    //      if (date('m', $time)==$bulan)
    //      {
    //          $list[]=array(
    //              'date'=>date('Y-m-d', $time),
    //              'day'=>DayIndonesia($time),
    //              'weekday' => (date('N',$time) == 6 || date('N',$time) == 7 ? 0 : 1),
    //          );   
    //      }
    //  }
    //  return $list;
    // }
    // docs : http://php.net/manual/en/function.date-diff.php
    function dateDifference($date_1, $date_2, $differenceFormat = '%a')
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format($differenceFormat);
    }


    function educrypt($crypt = array())
    {
        $output = false;
        $encrypt_method = "AES-256-CFB8";


        if (is_array($crypt)) {



            if ((isset($crypt['cid']) && $crypt['cid'] <> '') && (isset($crypt['secret']) && $crypt['secret'] <> '')) {


                $secret_f = $crypt['cid'];
                $secret_s = $crypt['secret'];

                if (isset($crypt['data']) && is_string($crypt['data'])) {




                    $string = $crypt['data'];
                    if (is_string($secret_f) && is_string($secret_s)) {


                        $key = hash('sha256', $secret_f);
                        $iv = substr(hash('sha256', $secret_s), 0, 16);

                        if (isset($crypt['action']) && $crypt['action'] <> '') {


                            if ($crypt['action'] == 'encrypt') {
                                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                                $output = str_replace("=", $secret_f, base64_encode($output));
                            } else if ($crypt['action'] == 'decrypt') {
                                $output = openssl_decrypt(base64_decode(str_replace($secret_f, "=", $string)), $encrypt_method, $key, 0, $iv);
                            }
                        }
                    }
                }
            }
        }

        return $output;
    }


    public function nourut($no)
    {
        $arrayLength = $no;
        $nominal = 3000000;
        $biaya = $nominal / $no;

        $i = 1;
        while ($i < $arrayLength + 1) {
            $data_no[] = $i;
            $i++;
        }

        // $data = array(
        //     'cicilan'=>$data_no,
        //     'biaya'=>$biaya,
        // );

        return $data_no;
    }


    function bulk($data = array(), $pages = null)
    {
        error_reporting(0);

        if ($pages) {
            $page = $pages - 1;
        } else {
            $page = 0;
        }

        if ($limits) {
            $limit = $limits;
        } else {
            $limit = 10;
        }

        $totaldata = count($data);
        $offset = $limit * $page;
        $data_json = array_slice($data, $offset, $limit);
        $hingga = round($totaldata / $limit);
        $totalhalaman = count($data_json);


        $this->bulk_pages($data_json);


        //echo json_encode($this->show_data_object_pagination(1,$hingga,$totaldata,$totalhalaman,$data_json));




        //echo count($data);
        // echo "<pre>";
        // print_r($data_json);

    }


    function bulk_pages($data = array())
    {
        // echo "string";



        if (count($data) == 10) {
            //insert
            echo "<pre>";
            print_r($data);
        } else {
            //puter
            // echo "tes";
            echo "<pre>";
            print_r($data);
            $this->bulk($data, 4);
        }
    }
    public function nama_kelas($id_kelas)
    {

        error_reporting(0);
        $kelas = DB::table('conf_kumkod')
            ->where('id', $id_kelas)
            ->first();

        if ($kelas->nama) {
            $nama_kelas = $kelas->nama;
        } else {
            $nama_kelas = null;
        }

        return $nama_kelas;
    }

    public function nama_mata_kuliah($id_mata_kuliah)
    {

        error_reporting(0);
        $matkul = DB::table('m_matakuliah')
            ->where('id_matakuliah', $id_mata_kuliah)
            ->first();

        if ($matkul->nama_matakuliah) {
            $nama_matkul = $matkul->nama_matakuliah;
        } else {
            $nama_matkul = null;
        }

        return $nama_matkul;
    }


    public function kouta_prodi($jurusan1, $id_kelas, $id_kelompok_jenjang)
    {
        $kouta = DB::table('m_program_studi as p')
            ->select('kouta')
            ->where('kode', $jurusan1)
            ->where('id_kelas', (int)$id_kelas)
            ->where('id_kelompok_jenjang', (int)$id_kelompok_jenjang)
            ->first();

        return $kouta->kouta;
    }

    public function kouta_terpenuhi($jurusan1, $id_kelas, $id_kelompok_jenjang)
    {

        $kouta_mhs = DB::table('mahasiswa as p')
            ->select('id', 'nama')
            ->where('jurusan1', $jurusan1)
            ->where('id_kelas', $id_kelas)
            ->where('id_kelompok_jenjang', $id_kelompok_jenjang)
            ->where('pmb_status', 'lulus')
            ->get();

        return $kouta_mhs;
    }
    public function nama_kelas_abc($id_kelas)
    {

        error_reporting(0);
        $kelas = DB::table('m_kelas')
            ->where('id_kelas', $id_kelas)
            ->first();

        if ($kelas->nama_kelas) {
            $nama_kelas = $kelas->nama_kelas;
        } else {
            $nama_kelas = null;
        }

        return $nama_kelas;
    }
    public function nama_ruangan($id_ruangan)
    {

        error_reporting(0);
        $ruangan = DB::table('m_ruangan')
            ->where('id_ruangan', $id_ruangan)
            ->first();

        if ($ruangan->nama_ruangan) {
            $nama_ruangan = $ruangan->nama_ruangan;
        } else {
            $nama_ruangan = null;
        }

        return $nama_ruangan;
    }
    public function nama_semester($id_semester)
    {

        error_reporting(0);
        $semester = DB::table('m_semester')
            ->where('id_semester', $id_semester)
            ->first();

        if ($semester->nama_semester) {
            $nama_semester = $semester->nama_semester;
        } else {
            $nama_semester = null;
        }

        return $nama_semester;
    }
    public function nama_wilayah($kode)
    {

        error_reporting(0);
        $wilayah = DB::table('m_wilayah')
            ->where('kode', $kode)
            ->first();

        if ($wilayah->nama) {
            $nama_wilayah = $wilayah->nama;
        } else {
            $nama_wilayah = null;
        }

        return $nama_wilayah;
    }
    public function nama_agama($id)
    {

        error_reporting(0);
        $agama = DB::table('m_agama')
            ->where('id_agama', $id)
            ->first();

        if ($agama->nm_agama) {
            $nama_agama =  $agama->nm_agama;
        } else {
            $nama_agama = null;
        }

        return $nama_agama;
    }
    public function nama_jenis_tinggal($id)
    {

        error_reporting(0);
        $jenis_tinggal = DB::table('m_jenis_tinggal')
            ->where('id_jns_tinggal', $id)
            ->first();

        if ($jenis_tinggal->nm_jns_tinggal) {
            $nama_jenis_tinggal =  $jenis_tinggal->nm_jns_tinggal;
        } else {
            $nama_jenis_tinggal = null;
        }

        return $nama_jenis_tinggal;
    }
    public function nama_jenjang_didik($id)
    {

        error_reporting(0);
        $didik = DB::table('m_jenjang_pendidikan')
            ->where('id_jenj_didik', $id)
            ->first();

        if ($didik->nm_jenj_didik) {
            $nama_jenjang_didik =  $didik->nm_jenj_didik;
        } else {
            $nama_jenjang_didik = null;
        }

        return $nama_jenjang_didik;
    }
    public function nama_pekerjaan($id)
    {

        error_reporting(0);
        $nama = DB::table('m_pekerjaan')
            ->where('id_pekerjaan', $id)
            ->first();

        if ($nama->nm_pekerjaan) {
            $nama_pekerjaan =  $nama->nm_pekerjaan;
        } else {
            $nama_pekerjaan = null;
        }

        return $nama_pekerjaan;
    }
    public function nama_penghasilan($id)
    {

        error_reporting(0);
        $nama = DB::table('m_penghasilan')
            ->where('id_penghasilan', $id)
            ->first();

        if ($nama->nm_penghasilan) {
            $nama_penghasilan =  $nama->nm_penghasilan;
        } else {
            $nama_penghasilan = null;
        }

        return $nama_penghasilan;
    }
    public function nama_kewarganegaraan($kode)
    {

        error_reporting(0);
        $negara = DB::table('m_negara')
            ->where('kode', $kode)
            ->first();

        if ($negara->nama) {
            $nama_negara =  $negara->nama;
        } else {
            $nama_negara = null;
        }

        return $nama_negara;
    }
    public function nama_jalur_masuk($id)
    {

        error_reporting(0);
        $nama = DB::table('m_jalur_masuk')
            ->where('id', $id)
            ->first();

        if ($nama->jalur) {
            $nama_jalur = $nama->jalur;
        } else {
            $nama_jalur = null;
        }

        return $nama_jalur;
    }
    public function nama_dosen($id_dosen)
    {

        error_reporting(0);
        $nama = DB::table('m_dosen')
            ->where('id_dosen', $id_dosen)
            ->first();

        if ($nama->nama_dosen) {
            $nama_dosen = $nama->nama_dosen;
        } else {
            $nama_dosen = null;
        }

        return $nama_dosen;
    }
    public function nama_gelombang($id_gelombang)
    {

        error_reporting(0);
        $nama = DB::table('pmb_gelombang')
            ->where('id_gelombang', $id_gelombang)
            ->first();

        if ($nama->nama_gelombang) {
            $nama_gelombang = $nama->nama_gelombang;
        } else {
            $nama_gelombang = null;
        }

        return $nama_gelombang;
    }
    public function just_jenjang_prodi($kode)
    {

        error_reporting(0);
        $jenjang = DB::table('m_jurusan')
            ->where('kode', $kode)
            ->select('jenjang')
            ->first();

        if ($jenjang->jenjang) {
            $nama_jenjang = $jenjang->jenjang;
        } else {
            $nama_jenjang = null;
        }

        return $nama_jenjang;
    }
}
