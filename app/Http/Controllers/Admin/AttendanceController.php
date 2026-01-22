<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;

class AttendanceController extends Controller
{
    //

    public function __construct(Request $request)
    {
        # code...
    }

    public function index(Request $request)
    {
        # code...
        $settings = DB::table('settings')->find(1);
        $google_api_key = $settings->google_api_key ?? '';

        $staff = User::select('id','name','email','mobile')->where('designation', 1)->get();
        $staff_id = !empty($request->staff_id)?$request->staff_id:'';
        $entry_date = !empty($request->entry_date)?$request->entry_date:'';
        $data = $user_attendances = $store_visited_locations = array();
        $locationArr = array();
        if(!empty($staff_id) && !empty($entry_date)){
            DB::enableQueryLog();
            $user_attendances = DB::table('user_attendances')->where('user_id',$staff_id)->where('start_date',$entry_date)->first();
            $data = DB::table('attendance_locations AS loc')
                    ->select('loc.*','stores.store_name')
                    ->leftJoin('stores', 'stores.id','loc.store_id')
                    ->where('loc.user_id',$staff_id)
                    ->where('loc.entry_date', $entry_date)
                    // ->whereRaw("entry_date = '".$entry_date."'")
                    ->get()->toarray();
            // dd(DB::getQueryLog($data));
            // dd($data);

            $last = count($data) - 1;
            
            foreach($data as $key => $value){
                // echo count($data); die;
                if(count($data) > 3 ){
                    if($key == 0){
                        unset($data[$key]);
                    }
                    if($key == $last){
                        unset($data[$key]);
                    }
                    
                    if(!in_array($key,[0,$last])){
                        $locationArr[] = $value->latitude.','.$value->longitude;
                    }
                }else{
                    $locationArr[] = $value->latitude.','.$value->longitude;
                }
                
                
            }
            // dd($locationArr);

            $store_visited_locations = DB::table('attendance_locations AS loc')
                                        ->select('loc.*','stores.store_name')
                                        ->leftJoin('stores', 'stores.id','loc.store_id')
                                        ->where('loc.user_id',$staff_id)
                                        ->where('loc.entry_date', $entry_date)
                                        ->where('loc.store_id', '!=', 0)
                                        ->get()->toarray();
        }
        // dd($data);
        return view('admin.attendance.list', compact('google_api_key','staff','staff_id','entry_date','data','user_attendances','locationArr','store_visited_locations'));
    }
}
