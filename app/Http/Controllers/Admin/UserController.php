<?php

namespace App\Http\Controllers\Admin;

use App\Interfaces\UserInterface;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Task;
use App\Models\Designation;
use App\Models\City;
use App\Models\UserCity;

class UserController extends Controller
{
    //  private UserInterface $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    /**
     * This method is for show user list
     *
     */

    public function index(Request $request, $userType = '')
    {
        // echo $userType; die;
        $term = !empty($request->term)?$request->term:'';

        $data = array();

        if($userType == 'supplier'){
            $data = Supplier::where('name','LIKE','%'.$term.'%')->orderBy('id','desc')->paginate(20);

            $data = $data->appends(['term'=>$term,'page'=>$request->page]);

            $total = Supplier::count();
        }

        return view('admin.user.index', compact('data', 'userType','term','total'));
    }

    /**
     * This method is for show user list
     *
     */
    public function staffList(Request $request)
    {
        $data = array();
        $term = !empty($request->term)?$request->term:'';     
        $designations = Designation::orderBy('name')->get()->toArray();   
        $designation = !empty($request->designation)?$request->designation:'';
        $data = User::select('users.*','designation.name AS designation_name')                
                ->leftJoin('designation', 'designation.id','users.designation')
                ->where('users.type',2);

        $total = User::where('type', 2);

        if(!empty($term)){
            $data = $data->where(function($q) use ($term){
                $q->where('users.name', 'LIKE', '%'.$term.'%');
            });
            $total = $total->where(function($q) use ($term){
                $q->where('users.name', 'LIKE', '%'.$term.'%');
            });
        }
        
        if(!empty($designation)){
            $data = $data->where(function($d) use ($designation){
                $d->where('designation', $designation);
            });
            $total = $total->where(function($d) use ($designation){
                $d->where('designation', $designation);
            });
        }        

        $data = $data->orderBy('users.id','desc')->paginate(20);
        $total = $total->count();

        $data = $data->appends([
            'page' => $request->page,
            'term' => $term,
            'designation' => $designation
        ]);

        return view('admin.staff.index', compact('data','term','total','designations','designation'));
    }

    /**
     * This method is for create user
     *
     */
    public function create(Request $request, $user_type)
    {
        // $data = $this->userRepository->listAll();
        return view('admin.user.create', compact('user_type'));
    }

    /**
     * This method is for edit user
     *
     */
    public function edit(Request $request, $userType, $id)
    {
        $userType = ($userType == 'customer') ? 1 : 2;
        if ($userType == 1) {
            $data = $this->userRepository->getCustomerById($id);
        }
        if ($userType == 2) {
            $data = $this->userRepository->getSupplierById($id);
        }
        return view('admin.user.edit', compact('userType', 'id', 'data'));
    }

    /**
     * This method is for create user
     *
     */
    public function store(Request $request)
    {
       //dd($request->all());
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "max:255",
           	'mobile' => 'required|digits_between:8,11',
            "whatsapp_no" => "required|unique:suppliers,whatsapp_no",
            "type" => "required",            
            // "gst_number" => "required",
            "shipping_address" => "required|string",
            "shipping_landmark" => "required|string",
            "shipping_state" => "required|string",
            "shipping_city" => "required|string",
            "shipping_pin" => "required|string",
            "shipping_country" => "required|string",
            "billing_address" => "required|string",
            "billing_landmark" => "required|string",
            "billing_state" => "required|string",
            "billing_city" => "required|string",
            "billing_pin" => "required|string",
            "billing_country" => "required|string",
            "gst_file" =>"nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);

        // dd($request->get('type'));

        $params = $request->except('_token');
        // dd($params);
        $storeData = $this->userRepository->create($params);
        $userType = ($request->get('type') == 1) ? 'customer' : 'supplier';
        if ($storeData) {
            Session::flash('message', 'Created successfully'); 
            return redirect()->route('admin.user.index', $userType);
        } else {
            return redirect()->route('admin.user.index', $userType)->withInput($request->all());
        }
    }

    public function createStaff(Request $request)
    {
        # code...
        // die('Hi');
        $designation = DB::table('designation')->where('status', 1)->orderBy('name','asc')->get();
        return view('admin.staff.create', compact('designation'));
    }

    /**
     * This method is for create Staff
     *
     */
    public function storeStaff(Request $request)
    {
        // dd($request->all());
        $request->validate([
            "designation" => "required|integer|min:1",
            "name" => "required|string|max:200",
            "email" => "nullable|string|max:200|unique:users,email",
            "password" => "required",
            "mobile" => "required|digits_between:8,11|unique:users,mobile",
            "whatsapp_no" => "required|max:10|unique:users,whatsapp_no",       
            "aadhar_no" => "required",
            // "account_holder_name" => "required",
            // "bank_name" => "required",
            // "branch_name" => "required",
            // "bank_account_no" => "required",
            // "ifsc" => "required",
            // "address" => "required|string",
            // "landmark" => "required|string",
            // "state" => "required|string",
            // "country" => "required|string",   
            // "city" => "required|string",
            // "pin" => "required",       
            "monthly_salary" => "required",
            // "daily_salary" => "required",
            // "travelling_allowance" => "required",            
            // "image" =>"required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            //"user_id_front" =>"required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            // "user_id_back" =>"required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
        ]);
        

        // dd($request->except('_token'));

        $params = $request->except('_token');
        // dd($params);
        $storeData = $this->userRepository->createStaff($params);

        if ($storeData) {
            Session::flash('message', 'Staff created successfully'); 
            return redirect()->route('admin.staff.index');
        } else {
            return redirect()->route('admin.staff.index')->withInput($request->all());
        }
    }
    /**
     * This method is for show user details
     * @param  $id
     *
     */
    public function show(Request $request, $id)
    {

        $data = $this->userRepository->listById($id);
      //  dd($data);
        return view('admin.user.detail', compact('data'));
    }
    /**
     * This method is for edit staff details
     * @param  $id
     *
     */
    public function editStaff(Request $request, $id)
    {
        $designation = DB::table('designation')->where('status', 1)->orderBy('name','asc')->get();
        $data = $this->userRepository->stafflistById($id);
        // dd($data);

        return view('admin.staff.edit', compact('data','designation'));
    }
    /**
     * This method is for staff update
     *
     *
    */
    public function staffupdate(Request $request, $id)
    {
        $request->validate([
            "designation" => "required|integer|min:1",
            "name" => "required|string|max:200",
            "email" => "nullable|string|max:200|unique:users,email,".$id,
            "mobile" => "required|digits_between:8,11|integer|unique:users,mobile,".$id,
            "whatsapp_no" => "required|unique:users,whatsapp_no,".$id,      
            "aadhar_no" => "required",
            // "account_holder_name" => "required",
            // "bank_name" => "required",
            // "branch_name" => "required",
            // "bank_account_no" => "required",
            // "ifsc" => "required",
            // "address" => "required|string",
            // "landmark" => "required|string",
            // "state" => "required|string",
            // "country" => "required|string",   
            // "city" => "required|string",
            // "pin" => "required",       
            "monthly_salary" => "required",
            // "daily_salary" => "required",
            // "travelling_allowance" => "required",            
            // "image" =>"required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            //"user_id_front" =>"required|mimes:jpg,jpeg,png,svg,gif|max:10000000",
            // "user_id_back" =>"required|mimes:jpg,jpeg,png,svg,gif|max:10000000",        
        ]);

        $params = $request->except('_token');
        $storeData = $this->userRepository->staffupdate($id, $params);
        // dd($storeData);
        if ($storeData) {
            Session::flash('message', 'Staff updated successfully'); 
            return redirect()->route('admin.staff.index');
        } else {
            return redirect()->route('admin.staff.create')->withInput($request->all());
        }
    }
    /**
     * This method is for show staff details
     * @param  $id
     *
    */
    public function staffshow(Request $request, $id)
    {

        $data = $this->userRepository->stafflistById($id);
        // dd($data);
        return view('admin.staff.details', compact('data'));
    }
    /**
     * This method is for update staff status
     * @param  $id
     *
     */
    public function staffstatus(Request $request, $id)
    {
        $storeData = $this->userRepository->stafftoggle($id);

        if ($storeData) {
            Session::flash('message', 'Status changed successfully'); 
            return redirect()->route('admin.staff.index');
        } else {
            return redirect()->route('admin.staff.index')->withInput($request->all());
        }
    }

    /**
     * This method is for user delete
     * @param  $id
     *
     */
    public function staffdestroy(Request $request, $id)
    {
        $this->userRepository->staffdelete($id);

        return redirect()->route('admin.staff.index');
    }
    /**
     * This method is for user update
     *
     *
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            "name" => "required|string|max:255",
            "email" => "max:255|",
            'mobile' => 'required|digits_between:8,11',
            "whatsapp_no" => "required|integer|unique:suppliers,whatsapp_no,".$id,
            "type" => "required",
            // "gst_number" => "required",
            "shipping_address" => "required|string",
            "shipping_landmark" => "required|string",
            "shipping_state" => "required|string",
            "shipping_city" => "required|string",
            "shipping_pin" => "required|string",
            "shipping_country" => "required|string",
            "billing_address" => "required|string",
            "billing_landmark" => "required|string",
            "billing_state" => "required|string",
            "billing_city" => "required|string",
            "billing_pin" => "required|string",
            "billing_country" => "required|string",
            "gst_file" =>"nullable|mimes:jpg,jpeg,png,svg,gif|max:10000000"
        ]);

        $params = $request->except('_token');
        // dd($params);

        $storeData = $this->userRepository->update($id, $params);
        // dd($storeData);
        $userType = ($request->get('type') == 1) ? 'customer' : 'supplier';
        if ($storeData) {
            Session::flash('message', 'Updated successfully'); 
            return redirect()->route('admin.user.index', $userType);
        } else {
            return redirect()->route('admin.user.index', $userType)->withInput($request->all());
        }
    }
    /**
     * This method is for update user status
     * @param  $id
     *
     */
    public function status(Request $request, $id, $userType)
    {

        $userTypeVal = ($userType == 'customer') ? 1 : 2;
        $storeData = $this->userRepository->toggle($id, $userTypeVal);
        if ($storeData) {
            Session::flash('message', 'Status changed successfully'); 
            return redirect()->route('admin.user.index', $userType);
        } else {
            return redirect()->route('admin.user.index', $userType)->withInput($request->all());
        }
    }
    /**
     * This method is for update user verification
     * @param  $id
     *
     */
    public function verification(Request $request, $id)
    {
        $storeData = $this->userRepository->verification($id);

        if ($storeData) {
            return redirect()->route('admin.user.index');
        } else {
            return redirect()->route('admin.user.index')->withInput($request->all());
        }
    }
    /**
     * This method is for user delete
     * @param  $id
     *
     */
    public function destroy(Request $request, $id, $userType)
    {
        $userTypeVal = ($userType == 'customer') ? 1 : 2;
        $this->userRepository->delete($id, $userTypeVal);
        return redirect()->route('admin.user.index', $userType);

    }

    public function bulkSuspend(Request $request)
    {      
        if(!empty($request->suspend_check)){
            $data = $this->userRepository->bulkSuspend($request->suspend_check);  
            $count_data = count($request->suspend_check);
            if($data){
                Session::flash('message', $count_data.' staffs suspended successfully'); 
                return redirect()->route('admin.staff.index');
            }else{
                return redirect()->route('admin.staff.index');
            }
        }else{
            return redirect()->route('admin.staff.index');
        }
        
          
    }

    public function supplierBulkSuspend(Request $request)
    {
        if(!empty($request->suspend_check)){
            $data = $this->userRepository->supplierBulkSuspend($request->suspend_check);  
            $count_data = count($request->suspend_check);
            if($data){
                Session::flash('message', $count_data.' staffs suspended successfully'); 
                return redirect('/admin/user/list/supplier');
            }else{
                return redirect('/admin/user/list/supplier');
            }
        }else{
            return redirect('/admin/user/list/supplier');
        }
    }

    public function listtask(Request $request, $id)
    {
        $term = !empty($request->term)?$request->term:'';
        $data = DB::table('tasks')->select('tasks.*')->selectRaw("(SELECT GROUP_CONCAT(stores.store_name) FROM task_details LEFT JOIN stores ON stores.id = task_details.store_id WHERE task_details.task_id = tasks.id) AS store_names")->where('tasks.user_id',$id)->orderBy('tasks.id','desc')->paginate(20);    
        $total = Task::where('user_id',$id)->count();
        $staff = DB::table('users')->find($id);
        $name = $staff->name;
        return view('admin.staff.listtask', compact('data','term','total','name','id'));
    }

    public function createtask(Request $request, $id)
    {
        $user_id = $id;
        $start_date = date("Y-m-d", strtotime("last sunday"));
        $end_date = date("Y-m-d", strtotime("next saturday"));
        $checkCurrentTaks = Task::where('user_id',$id)->where('start_date',$start_date)->where('end_date', $end_date)->first();
        if(empty($checkCurrentTaks)){
            $stores = Store::where('status', 1)->get();
            

            return view('admin.staff.createtask', compact('user_id','stores'));
        }else{
            return redirect()->route('admin.staff.edittask', [$user_id,$checkCurrentTaks->id]);
        }
        
    }

    public function savetask(Request $request)
    {
        // dd($request->all());
        $params = $request->except('_token');

        foreach(array_count_values($params['store']) as $val => $c){
            if($c > 1){
            
                return redirect()->route('admin.staff.createtask', $request->user_id)->withErrors(['error'=>'Same store is exist'])->withInput($request->all());
            }
    
        }
        

        $data = $this->userRepository->saveTask($params);
        
        if($data){
            Session::flash('message', 'Task created successfully'); 
            return redirect()->route('admin.staff.listtask', $request->user_id);
        }else{
            return redirect()->route('admin.staff.createtask', $request->user_id)->withInput($request->all());
        }
        
    }

    public function edittask(Request $request,$user_id,$id)
    {
        $today = date('Y-m-d');
        

        if(date('Y-m-d'))
        $data = DB::table('task_details AS td')->select('td.store_id','td.task_id','td.no_of_visit','td.comment','stores.store_name','t.start_date','t.end_date')->leftJoin('stores','stores.id','td.store_id')->leftJoin('tasks AS t','t.id','td.task_id')->where('td.task_id',$id)->get();
        $stores = Store::where('status', 1)->get();
        // dd($data);

        $start_date = $data[0]->start_date;
        $end_date = $data[0]->end_date;
        $is_editable = false;
        $disabled = "disabled";
        if (($today >= $start_date) && ($today <= $end_date)){
            $is_editable = true;
            $disabled = "";
        }


        return view('admin.staff.edittask', compact('user_id','id','data','stores','is_editable','disabled'));
    }

    public function updatetask(Request $request)
    {
        // dd($request->all());
        $params = $request->except('_token');
        // echo $request->user_id; die;

        foreach(array_count_values($params['store']) as $val => $c){
            if($c > 1){
            
                return redirect()->route('admin.staff.edittask', [$request->user_id,$request->id])->withErrors(['error'=>'Same store is exist'])->withInput($request->all());
            }    
        }        

        $data = $this->userRepository->updateTask($request->id,$params);

        if($data){
            Session::flash('message', 'Task updated successfully'); 
            return redirect()->route('admin.staff.listtask', $request->user_id);
        }else{
            return redirect()->route('admin.staff.createtask', $request->user_id)->withInput($request->all());
        }
        
        
    }

    public function show_areas($id,Request $request)
    {
        # Show assigned areas...
        $states = City::whereNull('parent_id')->get()->toArray();
        $user_cities = UserCity::with('city')->where('user_id',$id)->get()->toArray();
        $user = User::find($id);
        return view('admin.staff.area', compact('id','user_cities','states','user'));
    } 
    
    public function save_areas(Request $request)
    {
        # code...
        // dd($request->all());
        $request->validate([
            // 'monthly_collection_target_value' => 'required',
            'targeted_collection_amount_commission' => 'required',
            'city_ids' => 'required'
        ],[
            'city_ids.required' => 'Please add atleast one city',
            // 'monthly_collection_target_value.required' => 'Please Add Monthly Collection Target Value For Commission Eligiblity ',
            'targeted_collection_amount_commission.required' => 'Please Add Targeted Collection Commission '
        ]);
        $params = $request->except('_token');
        // dd($params);
        $city_ids = !empty($params['city_ids'])?$params['city_ids']:array();

        
        $all_prev_cities = UserCity::where('user_id',$params['user_id'])->get()->toArray();

        
        if(!empty($all_prev_cities)){
            foreach($all_prev_cities as $prev){
                if(!in_array($prev['city_id'],$city_ids)){
                    // echo "Deletable: ".$prev->role_id."<br/>";
                    UserCity::where('user_id',$params['user_id'])->where('city_id',$prev['city_id'])->delete();
                }
            }
        }
        foreach($city_ids as $city){            
            $existCities = UserCity::where('user_id',$params['user_id'])->where('city_id',$city)->first();
            
            if(!empty($existCities)){
            } else {
                UserCity::insert(['city_id'=>$city,'user_id'=>$params['user_id'], 'created_at'=>date('Y-m-d H:i:s')]);
            }            
        }

        User::where('id',$params['user_id'])->update([
            // 'monthly_collection_target_value' => $params['monthly_collection_target_value'],
            'targeted_collection_amount_commission' => $params['targeted_collection_amount_commission']
        ]);

        Session::flash('message', 'Saved successfully'); 
        return redirect()->route('admin.staff.show-areas', $params['user_id']);


    }

    public function logout_device($id)
    {
        ### Logout from mobile app ... mac_id make null

        User::where('id',$id)->update(['mac_id' => null]);

        Session::flash('message', 'User logged out from the device successfully'); 
        return redirect()->route('admin.staff.index');
    }

    
}
