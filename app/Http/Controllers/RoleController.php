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
use JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class RoleController extends Controller
{
    use utility;
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
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

            $kampus = $this->ambil_id_kampus($request->header('HTTP_FROM'));
            $auth = User::with(['user_role', 'user_role.role'])
                ->where("email", $request->email)
                ->where("password", $request->password)
                ->where("id_kampus", $kampus->id)
                ->first();

            if ($auth == null) {
                return response()->json($this->null_data());
            }

            $data = JWTAuth::encode(JWTFactory::sub($auth->id)->data([
                "id" => $auth->id,
                'id_kampus' => $auth->master_kampus->id,
                "role" => $auth->user_role->pluck('role.nama')
            ])->make())->get('value');

            return response()->json($this->show_data(0, 0, 0, 0, $data));
        } catch (Exception $e) {
            dd($e->getMessage());
            return response()->json($this->res_insert("fatal"));
        }
    }
    public function decode()
    {
        // try {
        $payload = collect(\JWTAuth::parseToken()->getPayload()->get('data'));
        dd($payload);
        return response()->json($this->show_data(0, 0, 0, 0, \JWTAuth::parseToken()->getPayload()->get('data')));
        // } catch (Exception $e) {
        //     dd($e->getMessage());
        //     return response()->json($this->res_insert("fatal"));
        // }
    }
    public function init(Request $request)
    {
        try {
            $x = $this->ambil_subdomain($request->header('HTTP_FROM'));
            return response()->json($this->show_data(0, 0, 0, 0, MasterKampus::where('kode_kampus', $x)->firstOrFail()));
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

            $kampus = $this->ambil_id_kampus($request->header('HTTP_FROM'));
            $check = User::where(["email" => $request->email, "id_kampus" => $kampus->id])->get()->count();
            if ($check) {
                return response()->json($this->res_insert("unique"));
            }

            DB::transaction(function () use ($request, $kampus) {
                $user = new User();
                $user->email = $request->email;
                $user->password = $request->password;
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
        try {
            $validator = $this->validate($request, [
                'nama_lengkap'      => 'required',
                'email'             => 'required',
                'nomor_wa'          => 'required',
                'asal_sekolah'      => 'required',
                'jurusan_diminati1' => 'required'
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

            $kampus = $this->ambil_id_kampus($request->header('HTTP_FROM'));
            $check = User::where(["email" => $request->email, "id_kampus" => $kampus->id])->get()->count();
            if ($check) {
                return response()->json($this->res_insert("unique"));
            }

            DB::transaction(function () use ($request, $kampus) {
                $user = new User();
                $user->email = $request->email;
                $user->password = Str::random(6);
                $user->id_kampus = $kampus->id;
                $user->save();

                $x = Role::select('id')->where('nama', 'user')->firstOrFail();

                $userRole = new UserRole();
                $userRole->id_auth = $user->id;
                $userRole->id_role = $x->id;
                $userRole->save();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            dd($e->getMessage());
            return response()->json($this->res_insert("fatal"));
        }
    }
    public function update_auth_user(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload()->get('data');
            $user = User::where(["id" => $payload->id, "id_kampus" => $payload->id_kampus])->firstOrFail();
            $kampus = $this->ambil_id_kampus();

            DB::transaction(function () use ($request, $kampus, $user) {
                $user->email                = $request->has('email') ? $request->email : $user->email;
                $user->nama_lengkap         = $request->has('nama_lengkap') ? $request->nama_lengkap : $user->nama_lengkap;
                $user->email                = $request->has('email') ? $request->email : $user->email;
                $user->nomor_wa             = $user->has('nomor_wa') ? $user->nomor_wa : $request->nomor_wa;
                $user->asal_sekolah         = $user->has('asal_sekolah') ? $user->asal_sekolah : $request->asal_sekolah;
                $user->jurusan_diminati1    = $user->has('jurusan_diminati1') ? $user->jurusan_diminati1 : $request->jurusan_diminati1;
                $user->foto                 = $user->has('foto') ? $user->foto : $request->foto;
                if ($request->has('jurusan_diminati2')) {
                    $user->jurusan_diminati1 = $request->jurusan_diminati2;
                }
                $user->save();
            });

            return response()->json($this->res_insert("sukses"));
        } catch (Exception $e) {
            // dd($e->getMessage());
            return response()->json($this->res_insert("fatal"));
        }
    }

    //funciton to_login

    function to_panel2(Request $request)
    {

        error_reporting(0);

        //$payload = JWTAuth::parseToken()->getPayload()->get('data');

        // // echo "<pre>";
        // // print_r($payload);
        // // die;

        // $id_kampus = $payload->id_kampus;
        // $id_auth = $payload->id;

        $id_kampus = 2;
        $id_auth   = 38;

        $panel = DB::table('m_panel')->get();



        foreach ($panel as $p) {

            $hak_kampus = DB::table('hak_akses_kampus')->where('id_kampus', $id_kampus)->where('id_panel', $p->id_panel)->first();



            if ($hak_kampus) {
                $status = 1;
                $hak_akses_role[$p->id_panel] = DB::table('hak_akses_panel')
                    ->join('role', 'role.id', '=', 'hak_akses_panel.id_role')
                    ->where('id_panel', $p->id_panel)
                    ->where('hak_akses_panel.id_auth', $id_auth)
                    ->select('nama as role')->get();

                foreach ($hak_akses_role[$p->id_panel] as $key => $r) {
                    $hak_akses[$p->id_panel][] = $r->role;
                }
            } else {
                $status = 0;
                $hak_akses_role = [];
                $hak_akses = [];
            }

            //

            if ($hak_akses[$p->id_panel]) {
                $hak_akses_role_new = $hak_akses[$p->id_panel];
            } else {
                $hak_akses_role_new = [];
            }

            $data_json[] = array(
                'nama_panel' => $p->nama_panel,
                'logo' => $p->logo,
                'status' => $status,
                'hak_akses' => $hak_akses_role_new,

            );
        }



        return response()->json($this->show_data_object($data_json));
    }


    function to_panel(Request $request)
    {

        error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;


                // $id_kampus = 1;
                // $id_auth   = 35;
                $hak_kampus_array = [];
                $hak_kampus = DB::table('hak_akses_kampus')
                    ->where('id_kampus', $id_kampus)
                    ->select('id_kampus', 'id_panel')
                    ->get();

                foreach ($hak_kampus as $key => $r) {
                    $hak_kampus_array[$r->id_kampus][$r->id_panel] = 1;
                }






                $hak_akses_array = [];
                $hak_akses_role = DB::table('hak_akses_panel')
                    ->join('role', 'role.id', '=', 'hak_akses_panel.id_role')
                    // ->where('id_panel', $p->id_panel)
                    ->where('hak_akses_panel.id_auth', $id_auth)
                    ->select('nama', 'id_panel')->get();

                foreach ($hak_akses_role as $key => $r) {
                    $hak_akses_array[$r->id_panel][] = $r->nama;
                }

                //   echo "<pre>";
                //  print_r($hak_akses_array);
                // // echo implode(",", $hak_kampus_array);
                //  die;





                $panel = DB::table('m_panel')->get();


                // echo "<pre>";
                // print_r($hak_kampus_array[1]);
                // die;

                foreach ($panel as $p) {

                    // $hak_kampus = DB::table('hak_akses_kampus')->where('id_kampus', $id_kampus)->where('id_panel', $p->id_panel)->first();
                    if ($hak_kampus_array[$id_kampus][$p->id_panel] == 1) {
                        $status = 1;
                    } else {
                        $status = 0;
                        $hak_akses_role = [];
                        $hak_akses = [];
                    }

                    //

                    if ($hak_akses_array[$p->id_panel]) {
                        $hak_akses_role_new = $hak_akses_array[$p->id_panel];
                    } else {
                        $hak_akses_role_new = [];
                    }

                    $data_json[] = array(
                        'id_panel' => $p->id_panel,
                        'nama_panel' => $p->nama_panel,
                        'logo' => $p->logo,
                        'status' => $status,
                        'hak_akses' => $hak_akses_role_new,

                    );
                }

                echo json_encode($this->show_data_object($data_json));
            });
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


    function to_menu(Request $request)
    {
        error_reporting(0);
        try {
            // $headers = apache_request_headers(); //get header
            // $request->headers->set('Authorization', $headers['Authorization']);

            $payload            = JWTAuth::parseToken()->getPayload()->get('data');
            DB::transaction(function () use ($payload, $request) {
                $id_auth        = $payload->id;
                $id_kampus      = $payload->id_kampus;



                // $id_kampus = 1;
                // $id_panel = 2;
                // $id_role = 2;

                $id_panel = $request->id_panel;
                $id_role = $request->role;


                // if($id_role==1){
                //      $menu = DB::table('m_panel_menu')
                //  ->join('hak_akses_menu', 'hak_akses_menu.id_panel_menu', '=', 'm_panel_menu.id_menu_panel')
                //    ->join('m_menu', 'm_menu.id_menu', '=', 'm_panel_menu.id_menu')
                //  ->where('hak_akses_menu.id_kampus', $id_kampus)
                //  ->where('m_panel_menu.id_panel', $id_panel)
                //  ->where('hak_akses_menu.status', 1)
                //              //->where('hak_akses_menu.id_role', $id_role)
                //             // ->where('m_menu.status', 1)
                //  ->select('m_menu.id_menu')
                //  ->groupBy('m_menu.id_menu')
                // ->get();

                // }else{

                $menu = DB::table('m_panel_menu')
                    ->join('hak_akses_menu', 'hak_akses_menu.id_panel_menu', '=', 'm_panel_menu.id_menu_panel')
                    ->join('m_menu', 'm_menu.id_menu', '=', 'm_panel_menu.id_menu')
                    ->where('hak_akses_menu.id_kampus', $id_kampus)
                    ->where('m_panel_menu.id_panel', $id_panel)
                    ->where('hak_akses_menu.status', 1)
                    ->where('m_menu.status', 1)
                    ->where('hak_akses_menu.id_role', $id_role)

                    ->orderBy('m_panel_menu.urut','asc')
                    ->get();


                // }


                //echo json_encode($menu);


                foreach ($menu as $m) {
                    $data_json[$m->posisi][] = array(
                        "id_menu_panel" => $m->id_menu_panel,
                        "id_panel" => $m->id_panel,
                        "id_menu" => $m->id_menu,
                        "to_menu" => $m->to_menu,
                        // "id_hak_akses_menu"=> 1,
                        "id_kampus" => $m->id_kampus,
                        //"status"=> $m->status,
                        //"id_role"=> 1,
                        //"id_panel_menu"=> 1,
                        "nama_menu" => $m->nama_menu,
                        "picture" => $m->picture,
                        //"posisi"=> "Kiri",
                        //"level"=> 0,
                        "logo" => $m->logo,
                        //"id_role_tampilan"=> 2,
                        "nama_menu_eng" => $m->nama_menu_eng,
                        "keterangan" => $m->keterangan,
                    );
                }





                echo json_encode($this->show_data_object($data_json));
            });
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



    //
}
