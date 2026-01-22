<?php

namespace App\Repositories;

use App\Interfaces\CustomerInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerRepository implements CustomerInterface
{
    /**
     * This method is for show faq list
     *
     */
    public function listAll()
    {
        return Customer::all();
    }
    
    public function listById($id)
    {
        return Customer::findOrFail($id);
    }
    
    public function create(array $data)
    {
        $collectedData = collect($data);
        $newEntry = new Customer;
        $newEntry->name = $collectedData['name'];
        $newEntry->email = $collectedData['email'];
        $newEntry->mobile = $collectedData['mobile'];
        $newEntry->is_wa_same = $collectedData['is_wa_same'];
        $newEntry->whatsapp_no = $collectedData['whatsapp_no'];
        $newEntry->gst_number = $collectedData['gst_number'];
        $newEntry->credit_limit = $collectedData['credit_limit'];
        $newEntry->credit_days = $collectedData['credit_days'];
        $newEntry->billing_address = $collectedData['billing_address'];
        $newEntry->billing_landmark = $collectedData['billing_landmark'];
        $newEntry->billing_city = $collectedData['billing_city'];
        $newEntry->billing_state = $collectedData['billing_state'];
        $newEntry->billing_country = $collectedData['billing_country'];
        $newEntry->billing_pin = $collectedData['billing_pin'];
        $newEntry->is_billing_shipping_same = $collectedData['is_billing_shipping_same'];
        $newEntry->shipping_address = $collectedData['shipping_address'];
        $newEntry->shipping_landmark = $collectedData['shipping_landmark'];
        $newEntry->shipping_city = $collectedData['shipping_city'];
        $newEntry->shipping_state = $collectedData['shipping_state'];
        $newEntry->shipping_country = $collectedData['shipping_country'];
        $newEntry->shipping_pin = $collectedData['shipping_pin'];
        $is_gst_file_uploaded = $collectedData['is_gst_file_uploaded'];
        $newEntry->gst_file = "";
        if(!empty($is_gst_file_uploaded)){
            $upload_path = "uploads/customer/";
            $image = $collectedData['gst_file'];           
        
            $imageName = time().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $newEntry->gst_file= $upload_path.$uploadedImage;
        }

        $newEntry->created_at = date('Y-m-d H:i:s');
        $newEntry->save();

        return $newEntry;
    }
    
    public function update($id, array $newDetails)
    {
        $updatedEntry = Customer::findOrFail($id);
        $collectedData = collect($newDetails);
        $updatedEntry->name = $collectedData['name'];
        $updatedEntry->email = $collectedData['email'];
        $updatedEntry->mobile = $collectedData['mobile'];
        $updatedEntry->is_wa_same = $collectedData['is_wa_same'];
        $updatedEntry->whatsapp_no = $collectedData['whatsapp_no'];
        $updatedEntry->gst_number = $collectedData['gst_number'];
        $updatedEntry->credit_limit = $collectedData['credit_limit'];
        $updatedEntry->credit_days = $collectedData['credit_days'];
        $updatedEntry->billing_address = $collectedData['billing_address'];
        $updatedEntry->billing_landmark = $collectedData['billing_landmark'];
        $updatedEntry->billing_city = $collectedData['billing_city'];
        $updatedEntry->billing_state = $collectedData['billing_state'];
        $updatedEntry->billing_country = $collectedData['billing_country'];
        $updatedEntry->billing_pin = $collectedData['billing_pin'];
        $updatedEntry->is_billing_shipping_same = $collectedData['is_billing_shipping_same'];
        $updatedEntry->shipping_address = $collectedData['shipping_address'];
        $updatedEntry->shipping_landmark = $collectedData['shipping_landmark'];
        $updatedEntry->shipping_city = $collectedData['shipping_city'];
        $updatedEntry->shipping_state = $collectedData['shipping_state'];
        $updatedEntry->shipping_country = $collectedData['shipping_country'];
        $updatedEntry->shipping_pin = $collectedData['shipping_pin'];

        if (isset($newDetails['gst_file'])) {
            // dd('here');
            $upload_path = "uploads/customer/";
            $image = $collectedData['gst_file'];
            $imageName = time().".".mt_rand().".".$image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $updatedEntry->gst_file = $upload_path.$uploadedImage;
        }

        $updatedEntry->save();

        return $updatedEntry;
    }
    
    public function toggle($id){
        $updatedEntry = Customer::findOrFail($id);

        $status = ( $updatedEntry->status == 1 ) ? 0 : 1;
        $updatedEntry->status = $status;
        $updatedEntry->save();

        return $updatedEntry;
    }
    
    public function delete($id)
    {
        Customer::destroy($id);
    }

    public function bulkSuspend(array $array)
    {
        Customer::whereIn('id',$array)->update(['status'=>0]);
        return true;
    }
}
