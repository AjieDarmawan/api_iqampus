<?php

namespace App\Http\Controllers;

use App\Models\master_kampus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterKampusController extends Controller
{
    public function index(Request $req){
        return response()->json([
            "kode"=>200,
            "message"=>"",
            "prop"=>[
                "totalHalaman"=>0,
                "halaman"=>0,
                "dari"=>0,
                "hingga"=>0
            ],
            "listdata"=>master_kampus::where('id_kampus',"")->get()
        ]);
    }
}
