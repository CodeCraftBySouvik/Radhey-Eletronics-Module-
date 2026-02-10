<?php

namespace App\Repositories;

use App\Interfaces\StoreInterface;
use App\Models\Store;
use App\Traits\UploadAble;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Models\NoOrderReason;
use App\UserNoorderreason;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File; 
use App\Models\City;

class StoreRepository implements StoreInterface
{
    use UploadAble;
    /**
     * This method is for show store list
     *
     */
    public function listAll()
    {
        return Store::all();
    }

     /**
     * This method is for show user list
     *
     */
    public function listUsers()
    {
        return User::all();
    }

    /**
     * This method is for show store details
     * @param  $id
     *
     */
    public function listById($id)
    {
       return Store::where('id',$id)->first();
    }

    /**
     * This method is for show store details
     * @param  $id
     *
     */
    public function listBySlug($slug)
    {
       return Store::where('slug',$slug)->first();
    }

    /**
     * This method is for store delete
     * @param  $id
     *
     */
    public function delete($id)
    {
        Store::destroy($id);
    }

    /**
     * This method is for store create
     * @param array $data
     * return in array format
     */
    public function create(array $data)
    {
    //   dd($data);
        $collection = collect($data);
        $store = new Store;
        $city_id = null;

        ## Set New City ##
        $checkState = City::whereNull('parent_id')->where('name', 'LIKE', $collection['billing_state'])->first();
        if(!empty($checkState)){
            $parent_id = $checkState->id;
            $checkCity = City::where('parent_id',$parent_id)->where('name', 'LIKE', $collection['billing_city'])->first();
            
            if(empty($checkCity)){
                $city_id = City::insertGetId([
                    'parent_id' => $parent_id,
                    'name' => $collection['billing_city'],
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
            } else {
                $city_id = $checkCity->id;
            }
            
        } else {
            $parent_id = City::insertGetId([
                'name' => $collection['billing_state'],
                'created_at'=>date('Y-m-d H:i:s')
            ]);
            $city_id = City::insertGetId([
                'parent_id' => $parent_id,
                'name' => $collection['billing_city'],
                'created_at'=>date('Y-m-d H:i:s')
            ]);
        }
        ##################

        // dd($city_id);
        $store->city_id = $city_id;        
        $store->created_by = $collection['created_by']; 
        $store->store_name = !empty($collection['store_name'])?$collection['store_name']:''; 
        $store->bussiness_name = !empty($collection['bussiness_name'])?$collection['bussiness_name']:'';        
        $store->contact = !empty($collection['contact'])?$collection['contact']:'';
        $store->whatsapp = !empty($collection['whatsapp'])?$collection['whatsapp']:'';
        $store->is_wa_same = isset($collection['is_wa_same'])?$collection['is_wa_same']:0;
        $store->email = !empty($collection['email'])?$collection['email']:'';
        
        $store->gst_number = !empty($collection['gst_number'])?$collection['gst_number']:'';
        $store->credit_limit = !empty($collection['credit_limit'])?$collection['credit_limit']:'';
        $store->credit_days = !empty($collection['credit_days'])?$collection['credit_days']:'';
        $store->address_outstation = !empty($collection['address_outstation'])?$collection['address_outstation']:'';
        $store->billing_address = !empty($collection['billing_address'])?$collection['billing_address']:'';
        $store->billing_landmark = !empty($collection['billing_landmark'])?$collection['billing_landmark']:'';
        $store->billing_city = !empty($collection['billing_city'])?$collection['billing_city']:'';
        $store->billing_state = !empty($collection['billing_state'])?$collection['billing_state']:'';
        $store->billing_country = !empty($collection['billing_country'])?$collection['billing_country']:'';
        $store->billing_pin = !empty($collection['billing_pin'])?$collection['billing_pin']:'';
        $store->is_billing_shipping_same = isset($collection['is_billing_shipping_same'])?$collection['is_billing_shipping_same']:0;
        $store->shipping_address = !empty($collection['shipping_address'])?$collection['shipping_address']:'';
        $store->shipping_landmark = !empty($collection['shipping_landmark'])?$collection['shipping_landmark']:'';
        $store->shipping_city = !empty($collection['shipping_city'])?$collection['shipping_landmark']:'';
        $store->shipping_state = !empty($collection['shipping_state'])?$collection['shipping_state']:'';
        $store->shipping_country = !empty($collection['shipping_country'])?$collection['shipping_country']:'';
        $store->shipping_pin = !empty($collection['shipping_pin'])?$collection['shipping_pin']:'';
        $is_gst_file_uploaded = isset($collection['is_gst_file_uploaded'])?$collection['is_gst_file_uploaded']:0;
        
        if(in_array($collection['created_by'], [1,2])){
            $store->is_approved = 1;
            $store->created_from = 'admin';
        } else {
            $store->is_approved = 0; /* Yet to approve */
            $store->created_from = 'staff';
        }
        $store->gst_file = "";
        if(!empty($is_gst_file_uploaded)){
            $upload_path = "public/uploads/store/";
            $image = $collection['gst_file'];           
        
            $imageName = time().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->gst_file= $upload_path.$uploadedImage;
        }

        $store->visit_image = "";
        if(!empty($collection['visit_image'])){
            $upload_path_visit_image = "public/uploads/store/";
            $visit_image = $collection['visit_image'];           
            
            $visitimageName = time().".".$visit_image->getClientOriginalName();
            $visit_image->move($upload_path_visit_image, $visitimageName);
            $uploadedVisitImage = $visitimageName;
            $store->visit_image= $upload_path_visit_image.$uploadedVisitImage;
        }
        
        $slug = Str::slug($collection['store_name'], '-');
        $slugExistCount = Store::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);
        $store->slug = $slug; 
        $store->created_at = date('Y-m-d H:i:s');
        $otp = 1234;
        $store->otp = $otp;
        $store->save();

        return $store;
    }

    public function verify_otp(int $storeId, string $otp){
        $store = Store::where('id',$storeId)->where('otp',$otp)->first();
        if(!$store){
            return [
                'status' => false,
                'message' => 'Invalid Otp'
            ];
        }

        $store->otp_verified_at = now();
        $store->save();

        return [
            'status' => true,
            'message' => 'Otp Verified Sucessfully',
            'store' => $store
        ];
    }
    /**
     * This method is for store update
     * @param array $newDetails
     * return in array format
     */
    public function update($id, array $newDetails)
    {
        $upload_path = "public/uploads/store/";
        $store = Store::findOrFail($id);
        $collection = collect($newDetails);

        $city_id = null;

        ## Set New City ##
        $checkState = City::whereNull('parent_id')->where('name', 'LIKE', $collection['billing_state'])->first();
        if(!empty($checkState)){
            $parent_id = $checkState->id;
            $checkCity = City::where('parent_id',$parent_id)->where('name', 'LIKE', $collection['billing_city'])->first();
            
            if(empty($checkCity)){
                $city_id = City::insertGetId([
                    'parent_id' => $parent_id,
                    'name' => $collection['billing_city']
                ]);
            } else {
                $city_id = $checkCity->id;
            }
            
        } else {
            $parent_id = City::insertGetId([
                'name' => $collection['billing_state']
            ]);
            $city_id = City::insertGetId([
                'parent_id' => $parent_id,
                'name' => $collection['billing_city']
            ]);
        }
        ##################

        // dd($city_id);
        $store->city_id = $city_id;


        
        (!empty($newDetails['store_name'])) ? $store->store_name = $collection['store_name'] : '';
        (!empty($newDetails['bussiness_name'])) ? $store->bussiness_name = $collection['bussiness_name'] : '';
        (!empty($newDetails['contact'])) ? $store->contact = $collection['contact'] : '';
        (!empty($newDetails['is_wa_same'])) ? $store->is_wa_same = $collection['is_wa_same'] : '';
        (!empty($newDetails['whatsapp'])) ? $store->whatsapp = $collection['whatsapp'] : '';
        (!empty($newDetails['email'])) ? $store->email = $collection['email'] : '';

        (!empty($newDetails['gst_number'])) ? $store->gst_number = $collection['gst_number'] : '';
        (!empty($newDetails['credit_limit'])) ? $store->credit_limit = $collection['credit_limit'] : '';
        (!empty($newDetails['credit_days'])) ? $store->credit_days = $collection['credit_days'] : '';
        (!empty($newDetails['address_outstation'])) ? $store->address_outstation = $collection['address_outstation'] : '';
        (!empty($newDetails['billing_address'])) ? $store->billing_address = $collection['billing_address'] : '';
        (!empty($newDetails['billing_landmark'])) ? $store->billing_landmark = $collection['billing_landmark'] : '';
        (!empty($newDetails['billing_city'])) ? $store->billing_city = $collection['billing_city'] : '';
        (!empty($newDetails['billing_state'])) ? $store->billing_state = $collection['billing_state'] : '';
        (!empty($newDetails['billing_country'])) ? $store->billing_country = $collection['billing_country'] : '';
        (!empty($newDetails['billing_pin'])) ? $store->billing_pin = $collection['billing_pin'] : '';
        (!empty($newDetails['is_billing_shipping_same'])) ? $store->is_billing_shipping_same = $collection['is_billing_shipping_same'] : '';
        (!empty($newDetails['shipping_address'])) ? $store->shipping_address = $collection['shipping_address'] : '';
        (!empty($newDetails['shipping_landmark'])) ? $store->shipping_landmark = $collection['shipping_landmark'] : '';
        (!empty($newDetails['shipping_city'])) ? $store->shipping_city = $collection['shipping_city'] : '';
        (!empty($newDetails['shipping_state'])) ? $store->shipping_state = $collection['shipping_state'] : '';
        (!empty($newDetails['shipping_country'])) ? $store->shipping_country = $collection['shipping_country'] : '';
        (!empty($newDetails['shipping_pin'])) ? $store->shipping_pin = $collection['shipping_pin'] : '';


        if (isset($newDetails['gst_file'])) {
            
            # Delete previous one
            $old_trn_file = $store->gst_file;
            $file_path = public_path().'/'.$old_trn_file;            
            File::delete($file_path);

            $upload_path = "public/uploads/store/";
            $image = $newDetails['gst_file'];
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->gst_file = $upload_path.$uploadedImage;
        }

        // dd('outside');
        $store->save();
        return $store;
    }
    /**
     * This method is for  update store status
     * @param  $id
     *
     */
    public function toggle($id){
        $store = Store::findOrFail($id);
        $status = 0;
        if( $store->status == 0 ){
            $status = 1;
        } else if ( $store->status == 1 ) {
            $status = 0;
        } else if ( $store->status == 2 ) {
            $status = 1;    
        }
        // echo $status ; die;
        Store::where('id',$id)->update(['status'=>$status]);
        return $status;
    }


    /**
     * This method is to update  store details through API
     * @param str $id
     */
    public function storeupdate($id, array $newDetails)
    {
       // return Store::find($storeId)->update($newDetails);
        try {
            $data = Store::whereId($id)->update($newDetails);

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
     * This method is to submit no order reason
     *
     *
     */
    public function noorderreasonupdate(array $data)
    {
        // return Store::find($storeId)->update($newDetails);
        $collection = collect($data);
        $usernoorderreason = new UserNoorderreason();
        $usernoorderreason->user_id = $collection['user_id'];
        $usernoorderreason->store_id = $collection['store_id'];
        // $usernoorderreason->no_order_reason_id = $collection['no_order_reason_id'];
        $usernoorderreason->comment	 = $collection['comment'];
        $usernoorderreason->location = $collection['location'];
        $usernoorderreason->lat = $collection['lat'];
        $usernoorderreason->lng = $collection['lng'];

        $upload_path = "public/uploads/store/";
        if(!empty($collection['visit_image'])){
            // dd($collection);
            $image = $collection['visit_image'];           

            $imageName = time().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $usernoorderreason->visit_image= $upload_path.$uploadedImage;
        } else {
            $usernoorderreason->visit_image = '';
        }
        $usernoorderreason->created_at = date('Y-m-d H:i:s');
        $usernoorderreason->save();
        return $usernoorderreason;
    }

    /**
     * This method is to list no order reason
     *
     *
     */
    

    public function bulkSuspend(array $array)
    {
        Store::whereIn('id', $array)->update(['status' => 0]);
        return true;
    }
      
    
    
    
}
