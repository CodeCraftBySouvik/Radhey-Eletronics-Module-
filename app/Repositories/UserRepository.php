<?php

namespace App\Repositories;

use App\Interfaces\UserInterface;
use App\User;
use App\Models\StaffPostDetail;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Address;
use App\Models\Distributor;
use App\Models\Task;
use App\Activity;
use App\StoreVisit;
use App\UserAttendance;
use App\StartEndDay;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File; 

class UserRepository implements UserInterface
{
    /**
     * This method is for show user list
     *
     */
    public function listAll()
    {
        return User::all();
    }

    /**
     * This method is for show customer list
     *
     */
    public function listAllCustomer()
    {
        return Customer::all();
    }

    /**
     * This method is for find customer by id
     *
     */
    public function getCustomerById($id)
    {
        return Customer::find($id);
    }

    /**
     * This method is for show Supplier list
     *
     */
    public function listAllSupplier()
    {
        return Supplier::all();
    }

    /**
     * This method is for search Supplier/customer list
     *
     */
    public function customSearch($userType, $term)
    {
        if ($userType == 1) {
            $updatedEntry = Customer::where('name', 'LIKE', '%' . $term . '%')
                ->orWhere('email', 'LIKE', '%' . $term . '%')
                ->get();
        }
        if ($userType == 2) {
            $updatedEntry = Supplier::where('name', 'LIKE', '%' . $term . '%')
                ->orWhere('email', 'LIKE', '%' . $term . '%')
                ->get();
        }
        return $updatedEntry;
    }

    /**
     * This method is for find customer by id
     *
     */
    public function getSupplierById($id)
    {
        return Supplier::find($id);
    }

    /**
     * This method is for show user details
     * @param  $id
     *
     */
    public function listById($id)
    {
       $user=  Supplier::where('id',$id)->get();
    //     $StaffPostDetail = StaffPostDetail::where('user_id', $id);
    //    dd($user);
    //    return User::where('id', $id)->get();
       return $user;
    }
 /**
     * This method is for show staff details
     * @param  $id
     *
     */
    public function stafflistById($id)
    {
       //$user=  User::where('id',$id)->get();
        //  $StaffPostDetail = StaffPostDetail::where('staff_id', $id)->with('user')->get();
    //    dd($user);
       return DB::table('users')->select('users.*','designation.name AS designation_name')->leftJoin('designation','designation.id','users.designation')->where('users.id',$id)->first();
      // return $user;
    //    return $StaffPostDetail;
    }
    /**
     * @param $id
     * @return mixed
     */
    public function detailsUser($id)
    {
        $categories = User::where('id',$id)->get();

        return $categories;
    }

    /**
     * This method is for create user
     *
     */
    public function create(array $data)
    {
        $collectedData = collect($data);
        // dd($collectedData);
        if ($collectedData['type'] == 1) {
            $newEntry = new Customer;
            $newEntry->name = $collectedData['name'];
            // $newEntry->official_name = $collectedData['official_name'];
            $newEntry->email = $collectedData['email'];
            $newEntry->mobile = $collectedData['mobile'];
            $newEntry->whatsapp_no = $collectedData['whatsapp_no'];
            $newEntry->credit_limit = $collectedData['credit_limit'];
            $newEntry->credit_days = $collectedData['credit_days'];
            $newEntry->gst_number = $collectedData['gst_number'];

            $upload_path = "public/uploads/user/";
            $image = $collectedData['gst_file'];
            $imageName = time().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $newEntry->gst_file= $upload_path.$uploadedImage;

            $newEntry->billing_address = $collectedData['billing_address'];
            $newEntry->billing_landmark = $collectedData['billing_landmark'];
            $newEntry->billing_city = $collectedData['billing_city'];
            $newEntry->billing_state = $collectedData['billing_state'];
            $newEntry->billing_pin = $collectedData['billing_pin'];
            $newEntry->billing_country = $collectedData['billing_country'];

            $newEntry->shipping_address = $collectedData['shipping_address'];
            $newEntry->shipping_landmark = $collectedData['shipping_landmark'];
            $newEntry->shipping_city = $collectedData['shipping_city'];
            $newEntry->shipping_state = $collectedData['shipping_state'];
            $newEntry->shipping_pin = $collectedData['shipping_pin'];
            $newEntry->shipping_country = $collectedData['shipping_country'];
            $newEntry->created_at = date('Y-m-d H:i:s');
            $newEntry->save();
        }
        if ($collectedData['type'] == 2) {
            $newEntry = new Supplier;
            $newEntry->name = $collectedData['name'];
            $newEntry->email = $collectedData['email'];
            $newEntry->mobile = $collectedData['mobile'];
            $newEntry->is_wa_same = $collectedData['is_wa_same'];
            $newEntry->whatsapp_no = $collectedData['whatsapp_no'];
            // $newEntry->credit_limit = $collectedData['credit_limit'];
            // $newEntry->credit_days = $collectedData['credit_days'];
            $newEntry->gst_number = $collectedData['gst_number'];
            $is_gst_file_uploaded = $collectedData['is_gst_file_uploaded'];

            $newEntry->gst_file = "";
            if(!empty($is_gst_file_uploaded)){
                $upload_path = "public/uploads/user/";
                $image = $collectedData['gst_file'];           
            
                $imageName = time().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $newEntry->gst_file= $upload_path.$uploadedImage;
            }
            

            $newEntry->billing_address = $collectedData['billing_address'];
            $newEntry->billing_landmark = $collectedData['billing_landmark'];
            $newEntry->billing_city = $collectedData['billing_city'];
            $newEntry->billing_state = $collectedData['billing_state'];
            $newEntry->billing_pin = $collectedData['billing_pin'];
            $newEntry->billing_country = $collectedData['billing_country'];

            $newEntry->is_billing_shipping_same = $collectedData['is_billing_shipping_same'];

            $newEntry->shipping_address = $collectedData['shipping_address'];
            $newEntry->shipping_landmark = $collectedData['shipping_landmark'];
            $newEntry->shipping_city = $collectedData['shipping_city'];
            $newEntry->shipping_state = $collectedData['shipping_state'];
            $newEntry->shipping_pin = $collectedData['shipping_pin'];
            $newEntry->shipping_country = $collectedData['shipping_country'];
            $newEntry->created_at = date('Y-m-d H:i:s');
            $newEntry->save();
        }

        return $newEntry;
    }

    /**
     * This method is for create staff
     *
     */
    public function createStaff(array $data)
    {
        // dd($data);
        DB::beginTransaction();
        try {
            $collectedData = collect($data);
            $newEntry = new User;
            $newEntry->designation = $collectedData['designation'];
            $newEntry->name = $collectedData['name'];            
            $newEntry->email = $collectedData['email'];
            $newEntry->password = Hash::make($collectedData['password']);
            $newEntry->mobile = $collectedData['mobile'];
            $newEntry->whatsapp_no = $collectedData['whatsapp_no'];
            $newEntry->is_wa_same = $collectedData['is_wa_same'];
            $newEntry->aadhar_no = $collectedData['aadhar_no'];
            /* address */
            $newEntry->address = !empty($collectedData['address'])?$collectedData['address']:'';
            $newEntry->landmark = !empty($collectedData['landmark'])?$collectedData['landmark']:'';
            $newEntry->city = !empty($collectedData['city'])?$collectedData['city']:'';
            $newEntry->state = !empty($collectedData['state'])?$collectedData['state']:'';
            $newEntry->pin = !empty($collectedData['pin'])?$collectedData['pin']:'';
            $newEntry->country = !empty($collectedData['country'])?$collectedData['country']:'';
            
            /* banking */
            $newEntry->account_holder_name = !empty($collectedData['account_holder_name'])?$collectedData['account_holder_name']:'';
            $newEntry->bank_name = !empty($collectedData['bank_name'])?$collectedData['bank_name']:'';
            $newEntry->branch_name = !empty($collectedData['branch_name'])?$collectedData['branch_name']:'';            
            $newEntry->bank_account_no = !empty($collectedData['bank_account_no'])?$collectedData['bank_account_no']:'';
            $newEntry->ifsc = !empty($collectedData['ifsc'])?$collectedData['ifsc']:'';
            
            /* salary & allowance */
            $newEntry->monthly_salary = !empty($collectedData['monthly_salary'])?$collectedData['monthly_salary']:'';
            $newEntry->daily_salary = !empty($collectedData['daily_salary'])?$collectedData['daily_salary']:'';
            $newEntry->travelling_allowance = !empty($collectedData['travelling_allowance'])?$collectedData['travelling_allowance']:'';

            
            if(!empty($collectedData['image'])){
                $upload_path = "public/uploads/staff/";
                $image = $collectedData['image'];
                $imageName = time().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $newEntry->image= $upload_path.$uploadedImage;

            }
            if(!empty($collectedData['user_id_front'])){
                $upload_path = "public/uploads/staff/";
                $image = $collectedData['user_id_front'];
                $imageName = time().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $newEntry->user_id_front= $upload_path.$uploadedImage;

            }
            if(!empty($collectedData['user_id_back'])){
                $upload_path = "public/uploads/staff/";
                $image = $collectedData['user_id_back'];
                $imageName = time().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $newEntry->user_id_back= $upload_path.$uploadedImage;

            }
            $newEntry->type = 2;
            $newEntry->created_at = date('Y-m-d H:i:s');
            $newEntry->save();
            $id = $newEntry->id;
            /* create new record salary */
            DB::table('staff_salary')->insert([
                'user_id' => $id,
                'monthly_salary' => $newEntry->monthly_salary,
                'daily_salary' => $newEntry->daily_salary,
                'travelling_allowance' => $newEntry->travelling_allowance,
                'is_current' => 1
            ]);                      

            DB::commit();
            

            return $newEntry;
        } catch (\Throwable $th) {
            DB::rollback();
            // dd($th);
            throw $th;

        }
    }
    /**
     * This method is for staff update
     *
     *
    */
    public function staffupdate($id, array $newDetails)
    {        
        DB::beginTransaction();
        try{

            $upload_path = "public/uploads/staff/";
            $updatedEntry = User::findOrFail($id);
            $collectedData = collect($newDetails);
            $updatedEntry->designation = $collectedData['designation'];
            $updatedEntry->name = $collectedData['name'];            
            $updatedEntry->email = $collectedData['email'];
            $updatedEntry->mobile = $collectedData['mobile'];
            $updatedEntry->whatsapp_no = $collectedData['whatsapp_no'];
            $updatedEntry->is_wa_same = $collectedData['is_wa_same'];
            $updatedEntry->aadhar_no = $collectedData['aadhar_no'];
            /* address */
            $updatedEntry->address = !empty($collectedData['address'])?$collectedData['address']:'';
            $updatedEntry->landmark = !empty($collectedData['landmark'])?$collectedData['landmark']:'';
            $updatedEntry->city = !empty($collectedData['city'])?$collectedData['city']:'';
            $updatedEntry->state = !empty($collectedData['state'])?$collectedData['state']:'';
            $updatedEntry->pin = !empty($collectedData['pin'])?$collectedData['pin']:'';
            $updatedEntry->country = !empty($collectedData['country'])?$collectedData['country']:'';
            
            /* banking */
            $updatedEntry->account_holder_name = !empty($collectedData['account_holder_name'])?$collectedData['account_holder_name']:'';
            $updatedEntry->bank_name = !empty($collectedData['bank_name'])?$collectedData['bank_name']:'';
            $updatedEntry->branch_name = !empty($collectedData['branch_name'])?$collectedData['branch_name']:'';            
            $updatedEntry->bank_account_no = !empty($collectedData['bank_account_no'])?$collectedData['bank_account_no']:'';
            $updatedEntry->ifsc = !empty($collectedData['ifsc'])?$collectedData['ifsc']:'';
                        
            /* salary & allowance */
            /* track new salary record */
            if($updatedEntry->monthly_salary != $collectedData['monthly_salary'] || $updatedEntry->daily_salary != $collectedData['daily_salary'] || $updatedEntry->travelling_allowance != $collectedData['travelling_allowance']) {

                DB::table('staff_salary')->where('user_id',$id)->update(['is_current'=>0]);
                DB::table('staff_salary')->insert([
                    'user_id' => $id,
                    'monthly_salary' => $collectedData['monthly_salary'],
                    'daily_salary' => $collectedData['daily_salary'],
                    'travelling_allowance' => $collectedData['travelling_allowance'],
                    'is_current' => 1
                ]);
            }

            $updatedEntry->monthly_salary = !empty($collectedData['monthly_salary'])?$collectedData['monthly_salary']:'';
            $updatedEntry->daily_salary = !empty($collectedData['daily_salary'])?$collectedData['daily_salary']:'';
            $updatedEntry->travelling_allowance = !empty($collectedData['travelling_allowance'])?$collectedData['travelling_allowance']:'';

            

            if (isset($newDetails['image'])) {
                if(!empty($updatedEntry->image)){
                    $file_path = public_path().'/'.$updatedEntry->image;                    
                    File::delete($file_path);                   
                }
                $image = $collectedData['image'];
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $updatedEntry->image = $upload_path.$uploadedImage;
            }
            if (isset($newDetails['user_id_front'])) {
                if(!empty($updatedEntry->user_id_front)){
                    $file_path = public_path().'/'.$updatedEntry->user_id_front;                    
                    File::delete($file_path);                   
                }
                $image = $collectedData['user_id_front'];
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $updatedEntry->user_id_front = $upload_path.$uploadedImage;
            }
            if (isset($newDetails['user_id_back'])) {
                if(!empty($updatedEntry->user_id_back)){
                    $file_path = public_path().'/'.$updatedEntry->user_id_back;                    
                    File::delete($file_path);                   
                }
                $image = $collectedData['user_id_back'];
                $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $updatedEntry->user_id_back = $upload_path.$uploadedImage;
            }
            $updatedEntry->save();

            
            

            DB::commit();
            return $updatedEntry;

        } catch (\Throwable $th) {
        //    dd($th);
            throw $th;
            DB::rollback();
        }
        //return $allowance;
    }
    /**
     * This method is for user update
     *
     *
    */
    public function update($id, array $newDetails)
    {
        $upload_path = "public/uploads/user/";
        $collectedData = collect($newDetails);
        if ($collectedData['type'] == 1) {
            $updatedEntry = Customer::findOrFail($id);

            $updatedEntry->name = $collectedData['name'];
            // $updatedEntry->official_name = $collectedData['official_name'];
            $updatedEntry->email = $collectedData['email'];
            $updatedEntry->mobile = $collectedData['mobile'];
            $updatedEntry->whatsapp_no = $collectedData['whatsapp_no'];
            $updatedEntry->credit_limit = $collectedData['credit_limit'];
            $updatedEntry->credit_days = $collectedData['credit_days'];
            $updatedEntry->gst_number = $collectedData['gst_number'];

            $updatedEntry->billing_address = $collectedData['billing_address'];
            $updatedEntry->billing_landmark = $collectedData['billing_landmark'];
            $updatedEntry->billing_city = $collectedData['billing_city'];
            $updatedEntry->billing_state = $collectedData['billing_state'];
            $updatedEntry->billing_pin = $collectedData['billing_pin'];
            $updatedEntry->billing_country = $collectedData['billing_country'];

            $updatedEntry->shipping_address = $collectedData['shipping_address'];
            $updatedEntry->shipping_landmark = $collectedData['shipping_landmark'];
            $updatedEntry->shipping_city = $collectedData['shipping_city'];
            $updatedEntry->shipping_state = $collectedData['shipping_state'];
            $updatedEntry->shipping_pin = $collectedData['shipping_pin'];
            $updatedEntry->shipping_country = $collectedData['shipping_country'];
            $updatedEntry->save();
        }
        if ($collectedData['type'] == 2) {
            $updatedEntry = Supplier::findOrFail($id);
            $updatedEntry->name = $collectedData['name'];
            $updatedEntry->email = $collectedData['email'];
            $updatedEntry->mobile = $collectedData['mobile'];
            $updatedEntry->is_wa_same = $collectedData['is_wa_same'];
            $updatedEntry->whatsapp_no = $collectedData['whatsapp_no'];
            // $updatedEntry->credit_limit = $collectedData['credit_limit'];
            // $updatedEntry->credit_days = $collectedData['credit_days'];
            $updatedEntry->gst_number = $collectedData['gst_number'];

            $updatedEntry->billing_address = $collectedData['billing_address'];
            $updatedEntry->billing_landmark = $collectedData['billing_landmark'];
            $updatedEntry->billing_city = $collectedData['billing_city'];
            $updatedEntry->billing_state = $collectedData['billing_state'];
            $updatedEntry->billing_pin = $collectedData['billing_pin'];
            $updatedEntry->billing_country = $collectedData['billing_country'];

            $updatedEntry->is_billing_shipping_same = $collectedData['is_billing_shipping_same'];

            $updatedEntry->shipping_address = $collectedData['shipping_address'];
            $updatedEntry->shipping_landmark = $collectedData['shipping_landmark'];
            $updatedEntry->shipping_city = $collectedData['shipping_city'];
            $updatedEntry->shipping_state = $collectedData['shipping_state'];
            $updatedEntry->shipping_pin = $collectedData['shipping_pin'];
            $updatedEntry->shipping_country = $collectedData['shipping_country'];
            $updatedEntry->save();
        }

        if (isset($newDetails['gst_file'])) {
            // dd('here');
            $image = $collectedData['gst_file'];
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $updatedEntry->gst_file = $upload_path.$uploadedImage;
        }

        $updatedEntry->save();

        return $updatedEntry;
    }
    /**
     * This method is for update user status
     * @param  $id
     *
     */
    public function toggle($id, $userType){
        if ($userType == 1) {
            $updatedEntry = Customer::findOrFail($id);
        }
        if ($userType == 2) {
            $updatedEntry = Supplier::findOrFail($id);
        }

        $status = ( $updatedEntry->status == 1 ) ? 0 : 1;
        $updatedEntry->status = $status;
        $updatedEntry->save();

        return $updatedEntry;
    }

     /**
     * This method is for update staff status
     * @param  $id
     *
     */
    public function stafftoggle($id){
        $updatedEntry = User::findOrFail($id);

        $status = ( $updatedEntry->status == 1 ) ? 0 : 1;
        $updatedEntry->status = $status;
        $updatedEntry->save();

        return $updatedEntry;
    }
    /**
     * This method is for update user verification
     * @param  $id
     *
     */
    public function verification($id){
        $updatedEntry = User::findOrFail($id);

        $is_verified = ( $updatedEntry->is_verified == 1 ) ? 0 : 1;
        $updatedEntry->is_verified = $is_verified;
        $updatedEntry->save();

        return $updatedEntry;
    }
    /**
     * This method is for staff delete
     * @param  $id
     *
     */
    public function staffdelete($id)
    {
        User::destroy($id);
    }


    /**
     * This method is for user delete
     * @param  $id
     *
     */
    public function delete($id, $userType){
        if ($userType == 1) {
            $updatedEntry = Customer::destroy($id);
        }
        if ($userType == 2) {
            $updatedEntry = Supplier::destroy($id);
        }
    }
    /**
     * This method is to get user activity details
     * @param str $id
     */
    public function useractivity(){
        //return Activity::all();
        $today = date("Y-m-d");
        return Activity::where('date', '=', $today)->get();
    }

    public function storeVisit(){
        return StoreVisit::latest('id', 'desc')->get();
    }


    //last 10 visit

    public function storeVisitlistByStore($storeId){
        return StoreVisit::where('store_id',$storeId)->where('created_at', '>', \Carbon\Carbon::now()->startOfDay())->latest('id', 'desc')->limit(10)->with('users')->get();
    }

    //last 10 visit

    public function storeVisitlistByUser($userId){
        return StoreVisit::where('user_id',$userId)->where('created_at', '>', \Carbon\Carbon::now()->startOfDay())->latest('id', 'desc')->limit(10)->with(['store_detail' => function ($query) {
            $query->select('id', 'store_name', 'contact', 'email', 'whatsapp');
        }])->get();
    }

    /**
     * This method is to get user activity details by Id
     * @param str $id
     */
    public function useractivitylistById($activityId){

        return Activity::where('id', $activityId)->with('users')->first();
    }
    /**
     * This method is to get user activity details
     * @param str $data
     * return array
     */
    public function useractivitycreate(array $data)
    {
        return Activity::create($data);
    }
    public function storeVisitCreate(array $data)
    {
        // dd($data);
        $dateTime = date('Y-m-d H:i:s');
        $existStartedVisitStore = StoreVisit::where('user_id',$data['user_id'])->where('store_id',$data['store_id'])->where('end_date', '=', null)->where('end_time', '=', null)->first();

        if(!empty($existStartedVisitStore)){
            // die($existStartedVisitStore->start_date);
            if($existStartedVisitStore->start_date < date('Y-m-d')){
                $store_visit_details = DB::table('store_visit_details')->where('visit_id',$existStartedVisitStore->id)->orderBy('id','desc')->first();
                StoreVisit::where('id',$existStartedVisitStore->id)->update([
                    'end_date' => date('Y-m-d'),
                    'end_time' => date('H:i'),
                    'end_latitude' => $store_visit_details->latitude,
                    'end_longitude' => $store_visit_details->longitude,
                    'remarks' => "User didn't end visit preperly on the day."
                ]);
            }else{
                return array('status'=>false,'message'=>"An existing visit is ongoing");
            }
        }


        
        $store_vist =  StoreVisit::create($data);
        $store_visit_details = DB::table('store_visit_details')->insert(['visit_id'=>$store_vist->id,'latitude'=>$data['start_latitude'],'longitude'=>$data['start_longitude'],'created_at'=>$dateTime,'updated_at'=>$dateTime]);

        return array('status'=>true,'store_vist'=>$store_vist);
    }

    /**
     * This method is to update user activity details
     * @param str $id
     */
    public function useractivityupdate($id, array $newDetails)
    {
        // return Activity::find($activityId)->update($newDetails);
        try {
            $data = Activity::whereId($id)->update($newDetails);

            // if ($data) {
                $resp = ['error' => false, 'message' => 'Data updated successfully'];
            // } else {
            //     $resp = ['error' => true, 'message' => 'Something happened'];
            // }
        } catch (\Throwable $th) {
            $resp = ['error' => true, 'message' => $th];
        }
        return $resp;
    }

    /**
     * This method is to update store visit
     * @param str $id
     */
    public function storevisitupdate($id, array $newDetails)
    {
        // return Activity::find($activityId)->update($newDetails);
        try {
            $data = StoreVisit::whereId($id)->update($newDetails);

            // if ($data) {
                $resp = ['error' => false, 'message' => 'Data updated successfully'];
            // } else {
            //     $resp = ['error' => true, 'message' => 'Something happened'];
            // }
        } catch (\Throwable $th) {
            $resp = ['error' => true, 'message' => $th];
        }
        return $resp;
    }

    /**
     * This method is to delete user activity details
     *
     */
    public function useractivitydelete($activityId)
    {
        try {
        Activity::findOrFail($activityId)->delete();
        $resp = ['error' => false, 'message' => 'Data Deleted successfully'];
            // } else {
            //     $resp = ['error' => true, 'message' => 'Something happened'];
            // }
        } catch (\Throwable $th) {
            $resp = ['error' => true, 'message' => $th];
        }
        return $resp;
    }


    /**
     * This method is to get user attendance details
     *
     */
    public function userattendance()
    {
        return UserAttendance::all();
    }
    /**
     * This method is to get user attendance details by Id
     * @param str $id
     */
    public function userattendanceById($id)
    {
        return UserAttendance::where('id', $id)->with('users')->first();
    }
    /**
     * This method is to create user attendance details
     * @param str $data
     * return array
     */
    public function userattendancecreate(array $data)
    {
        return UserAttendance::create($data);
    }
    /**
     * This method is to update user attendance details
     * @param str $id
     */
    public function userattendanceupdate(int $id,array $newDetails)
    {


        try {
            $data = UserAttendance::whereId($id)->update($newDetails);

            // if ($data) {
                $resp = ['error' => false, 'message' => 'Data updated successfully'];
            // } else {
            //     $resp = ['error' => true, 'message' => 'Something happened'];
            // }
        } catch (\Throwable $th) {
            $resp = ['error' => true, 'message' => $th];
        }
        return $resp;
    }
    /**
     * This method is to delete user attendance details
     *
     */
    public function userattendancedelete($id)
    {
        UserAttendance::destroy($id);
    }


    /**
     * This method is to get user start day end day details
     *
     */
    public function userday()
    {
        return StartEndDay::all();
    }
    /**
     * This method is to get user start day end day details by Id
     * @param str $id
     */
    public function userdaylistById($id)
    {
        return StartEndDay::where('id', $id)->with('users')->first();
    }
    /**
     * This method is to create user start day end day details
     * @param str $data
     * return array
     */
    public function userdaycreate(array $data)
    {
        return StartEndDay::create($data);
    }
    /**
     * This method is to update user start day end day details
     * @param str $id
     */
    public function userdayupdate(int $id, array $newDetails)
    {
        try {
            $data = StartEndDay::whereId($id)->update($newDetails);

            // if ($data) {
                $resp = ['error' => false, 'message' => 'Data updated successfully'];
            // } else {
            //     $resp = ['error' => true, 'message' => 'Something happened'];
            // }
        } catch (\Throwable $th) {
            $resp = ['error' => true, 'message' => $th];
        }
        return $resp;
    }
    /**
     * This method is to delete user start day end day details
     *
     */
    public function userdaydelete($id)
    {
        StartEndDay::destroy($id);
    }

    /**
     * This method is to check validation of mobile no and generate otp
     * @param str $mobileNo
     *
     */

    public function otpGenerate($mobileNo){
        $userExists = User::where('mobile', $mobileNo)->first();
        if ($userExists) {
            if($userExists->status == 1){                
                if($userExists->type == 2 &&  (!in_array($userExists->designation, [1,5])) ){
                    $resp = [
                        'error' => true, 
                        'message' => 'You have no access to use this app'
                    ];
                } else {
                    $otp = 1234;
                    // $otp = mt_rand(0, 10000);
                    $userExists->otp = $otp;
                    // sms gateway
                    $userExists->save();

                    $designation = "Partner";
                    if($userExists->designation == 1){
                        $designation = "Salesman";
                    } else if ($userExists->designation == 5){
                        $designation = "Godown Manager";
                    }

                    $resp = [
                        'error' => false, 
                        'message' => 'OTP generated successfully, you logged in as '.$designation.' ', 
                        'data' => [
                            'id' => $userExists->id,
                            'otp' => $otp
                        ]
                    ];
                }            
                
            } else {
                $resp = [
                    'error' => true, 
                    'message' => 'User is inactive'
                ];
            }
            

        } else {
            $resp = [
                'error' => true, 
                'message' => 'User does not exist'
            ];

        }
        return $resp;
    }

    /**
     * This method is to check validation of otp
     * @param str $otp
     *
     */
    public function otpcheck($mobile , $otp, $lat, $long)
    {
        $userExists = User::where('mobile', $mobile)->where('otp', $otp)->first();

        if ($userExists) {            

            $id = $userExists->id;
            $user = DB::table('users AS u')->select('u.id','u.name','u.whatsapp_no','u.mobile','u.image','u.designation','designation.name AS designation_name','u.type')->leftJoin('designation', 'designation.id','u.designation')->where('u.id',$id)->first();
            
            $total_payment_collection = $total_commission_earned = 0;
            if($userExists->type == 2){
                $total_payment_collection = DB::table('payment_collections')->where('user_id',$id)->where('is_ledger_added', 1)->sum('collection_amount');

                $total_commission_earned = DB::table('ledger')->where('user_type','staff')->where('staff_id',$id)->where('purpose', 'sales_order_payment_commission')->sum('transaction_amount');
            
            }

            
            $user->total_payment_collection = $total_payment_collection;   
            $user->total_commission_earned = $total_commission_earned; 

            $user->designation_name = !empty($user->designation_name)?$user->designation_name:'Partner';

            /* Make OTP blank */
            // User::where('id',$id)->update(['otp'=>'']);
            
            /* Staff Attendance With Location */
            // if($userExists->type == 2) { 
            //     $start_latitude = !empty($lat)?$lat:'';
            //     $start_longitude = !empty($long)?$long:'';

            //     $checkExistingAttendance = DB::table('user_attendances')->where('user_id',$id)->where('end_date','!=', null)->first();

            //     DB::table('user_attendances')->insert([
            //         'user_id' => $id,
            //         'start_date' => date('Y-m-d'),
            //         'start_time' => date('H:i:s'),
            //         'start_latitude' => $start_latitude,
            //         'start_longitude' => $start_longitude,
            //     ]);
            // }
            

            $resp = [
                'error' => false, 
                'message' => 'OTP matched. Logged in successfully', 
                'data' => $user
                // 'data' => $userExists
            ];
        } else {
            $resp = [
                'error' => true, 
                'message' => 'You have entered an wrong otp. Please try with the correct one.'
            ];
        }
        
        return $resp;
    }


    /**
     * This method is to get user  details by Id
     * @param str $id
     */
    public function userdetailsById($id){
        return User::where('id', $id)->first();
    }

    public function updateuserprofile($userId,$newDetails)
    {
            try {
            $data = User::whereId($userId)->update($newDetails);

            // if ($data) {
                $resp = ['error' => false, 'message' => 'Data updated successfully'];
            // } else {
            //     $resp = ['error' => true, 'message' => 'Something happened'];
            // }
        } catch (\Throwable $th) {
            $resp = ['error' => true, 'message' => $th];
        }
        return $resp;
    }

    /**
     * This method is to get user activity details
     * @param str $data
     * return array
     */
    public function distributorcreate(array $data)
    {
        return Distributor::create($data);
    }

     public function distributorlist(){
     return Distributor::all();

     }



     public function distributorview($id)
    {
        return Distributor::where('id', $id)->get();
    }
    // public function storeVisitCreate(array $data)
    // {
    //     return StoreVisit::create($data);
    // }

    public function bulkSuspend(array $array)
    {
        User::whereIn('id', $array)->update(['status' => 0]);
        return true;
    }


    public function supplierBulkSuspend(array $array)
    {
        Supplier::whereIn('id', $array)->update(['status' => 0]);
        return true;
    }

    public function allTasksUser($id)
    {
        

        $data = Task::select('*')->where('user_id', $id)->get();
        // $data = DB::select(DB::raw("SELECT tasks.*,(GROUP_CONCAT(stores.store_name)) AS store_name  FROM tasks JOIN stores ON stores.id IN (tasks.store_id_arr) WHERE tasks.user_id = ".$id." "));
        return $data;
    }

    public function saveTask($requestDetails)
    {        
        $task = new Task;
        $task->user_id = $requestDetails['user_id'];
        $task->start_date = $requestDetails['start_date'];
        $task->end_date = $requestDetails['end_date'];
        $task->created_at = date('Y-m-d H:i:s');
        $task->save();
        $id = $task->id;
        
        for($i=0; $i< count($requestDetails['store']);$i++){
            $task_id = $id;
            $store_id = $requestDetails['store_id'][$i];
            $no_of_visit = $requestDetails['no_of_visit'][$i];
            $comment = $requestDetails['comment'][$i];
            DB::table('task_details')->insert([
                'task_id' => $task_id,
                'store_id' => $store_id,
                'no_of_visit' => $no_of_visit,
                'comment' => $comment
            ]);
            
        }

        return true;

    }

    public function updateTask($id,$requestDetails)
    {
        DB::table('task_details')->where('task_id',$id)->delete();
        for($i=0; $i< count($requestDetails['store']);$i++){
            $task_id = $id;
            $store_id = $requestDetails['store_id'][$i];
            $no_of_visit = $requestDetails['no_of_visit'][$i];
            $comment = $requestDetails['comment'][$i];
            DB::table('task_details')->insert([
                'task_id' => $task_id,
                'store_id' => $store_id,
                'no_of_visit' => $no_of_visit,
                'comment' => $comment
            ]);
            
        }

        return true;

    }

}
