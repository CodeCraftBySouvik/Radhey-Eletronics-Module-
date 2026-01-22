<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    //

    public function index(Request $request,$user_id)
    {
        # user's store visits
        $user = User::find($user_id);
        $user_name = $user->name; 
        $data = DB::table('store_visits AS sv')->select('sv.*','s.store_name')->leftJoin('stores AS s','s.id','sv.store_id')->where('sv.user_id',$user_id)->orderBy('sv.id','desc')->paginate(20);


        return view('admin.storevisit.index', compact('data','user_name','user_id'));

    }

    public function details($visit_id)
    {
        $data = DB::table('store_visit_details AS svd')->select('svd.*','store_visits.user_id')->leftJoin('store_visits', 'store_visits.id','svd.visit_id')->where('svd.visit_id',$visit_id)->orderBy('svd.id','asc')->get()->toarray();
        

        return view('admin.storevisit.details', compact('data'));
    }

    public function mapview($visit_id)
    {
        # code...
        $data = DB::table('store_visit_details AS svd')->select('svd.*','store_visits.user_id')->leftJoin('store_visits', 'store_visits.id','svd.visit_id')->where('svd.visit_id',$visit_id)->orderBy('svd.id','asc')->get()->toarray();
        return view('admin.storevisit.map', compact('visit_id','data'));
    }

    public function mapdirection($visit_id)
    {
           # code...
        $store_visits = DB::table('store_visits')->find($visit_id);
        $waypoints = DB::table('store_visit_details AS svd')->where('svd.visit_id',$visit_id)->get();
        // echo count($waypoints); die;
        // echo count($waypoints)-1; die;
        $last = count($waypoints) - 1;
        $lastprev = count($waypoints) - 2;
        $locationArr = array();
        foreach($waypoints as $key => $value){
            if($key == 0){
                unset($waypoints[$key]);
            }
            if($key == $last){
                unset($waypoints[$key]);
            }
            if($key == $lastprev){
                unset($waypoints[$key]);
            }
            if(!in_array($key,[0,$last,$lastprev])){
                $locationArr[] = $value->latitude.','.$value->longitude;
            }
            
        }
        
        // dd($locationArr);
        return view('admin.storevisit.map1', compact('visit_id','store_visits','waypoints','locationArr'));
    }
}
