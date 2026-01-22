<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class DesignationController extends Controller
{
    // Staff Designations
    public function index(Request $request)
    {
        # code...

        $data = DB::table('designation')->select('*')->selectRaw("(SELECT COUNT(id) FROM users WHERE users.designation = designation.id) AS count_staff, (SELECT GROUP_CONCAT(roles.name) FROM user_roles LEFT JOIN roles ON roles.id = user_roles.role_id WHERE user_roles.designation_id = designation.id) AS role_names ")->orderBy('id','desc')->get();

        $roles = DB::table('roles')->orderBy('name','asc')->get();

        // dd($data);
        return view('admin.designation.index', compact('data','roles'));
    }

    public function store(Request $request)
    {
        # code...
        $request->validate([
            'name' => 'required|max:200|unique:designation,name'
        ]);

        $params = $request->except('_token');
        $name = $params['name'];
        $role_ids = !empty($params['role_ids'])?$params['role_ids']:array();
        
        $id = DB::table('designation')->insertGetId(['name'=>$name]);

        if(!empty($role_ids)){
        
            foreach($role_ids as $role_id){
                DB::table('user_roles')->insert(['designation_id'=>$id,'role_id'=>$role_id]);
            }
        }

        Session::flash('message', 'Designation created successfully');
        return redirect()->route('admin.designation.index');
        
    }

    public function show(Request $request, $id)
    {
        # code...
        $data = DB::table('designation')->find($id);
        $roles = DB::table('roles')->orderBy('name','asc')->get();
        $user_roles = DB::table('user_roles')->where('designation_id',$id)->get();
        $roleIdArr = array();
        if(!empty($user_roles)){
            foreach($user_roles as $r){
                $roleIdArr[] = $r->role_id;
            }
        }
        return view('admin.designation.detail', compact('data','roleIdArr','roles'));
    }

    public function update(Request $request, $id)
    {
        # code...
        $request->validate([
            'name' => 'required|max:200|unique:designation,name,'.$id
        ]);

        $params = $request->except('_token');
        $name = $params['name'];
        $role_ids = !empty($params['role_ids'])?$params['role_ids']:array();
        
        DB::table('designation')->where('id',$id)->update(['name'=>$name,'updated_at'=>date('Y-m-d H:i:s')]);
        $all_prev_roles = DB::table('user_roles')->select('role_id')->where('designation_id',$id)->get();

        
        if(!empty($all_prev_roles)){
            foreach($all_prev_roles as $prev){
                if(!in_array($prev->role_id,$role_ids)){
                    // echo "Deletable: ".$prev->role_id."<br/>";
                    DB::table('user_roles')->where('designation_id',$id)->where('role_id',$prev->role_id)->delete();
                }
            }
        }
        foreach($role_ids as $role){            
            $existRoles = DB::table('user_roles')->select('role_id')->where('designation_id',$id)->where('role_id',$role)->first();
            
            if(!empty($existRoles)){
            } else {
                DB::table('user_roles')->insert(['role_id'=>$role,'designation_id'=>$id]);
            }            
        }

        Session::flash('message', 'Designation updated successfully');
        return redirect()->route('admin.designation.index');
    }

    public function bulkSuspend(Request $request)
    {
        # code...
        if(!empty($request->suspend_check)){
            $count_data = count($request->suspend_check);
             
            DB::table('designation')->whereIn('id', $request->suspend_check)->update(['status' => 0]);
            
            Session::flash('message', $count_data.' designations suspended successfully');
            return redirect()->route('admin.designation.index');
            
        }else{
            return redirect()->route('admin.designation.index');
        }
    }

    public function status(Request $request, $id)
    {
        $designation = DB::table('designation')->find($id);
        if($designation->status == 1){
            $status = 0;
            $statusText = "Suspended";
        } else {
            $status = 1;
            $statusText = "Activated";
        }

        DB::table('designation')->where('id',$id)->update(['status'=>$status]);

        Session::flash('message', $statusText.' successfully');
        return redirect()->route('admin.designation.index');
        
    }

}
