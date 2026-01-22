<?php
namespace App\Repositories;

use App\Interfaces\PaymentCollectionInterface;
use App\Models\PaymentCollection;
use Illuminate\Support\Facades\DB;

class PaymentCollectionRepository implements PaymentCollectionInterface
{
    /**
     * This method is for show PaymentCollection list
     *
     */
    public function listAll()
    {
        return PaymentCollection::all();
    }

    /**
     * This method is for listing PaymentCollection by user
     * @param  $id
     *
     */
    public function listByUser($id)
    {
        return PaymentCollection::where('user_id', $id)->get();
    }

    /**
     * This method is for listing PaymentCollection store
     * @param  $id
     *
     */
    public function listByStore(array $array)
    {        
        $type = $array['type'];
        $take = !empty($array['take'])?$array['take']:100;
        $page = !empty($array['page'])?$array['page']:0;
        $skip = ($take*$page);
        $to_date = date('Y-m-d');
        if($type == 'today'){
            $from_date = date('Y-m-d');            
        }else if($type == 'lastweek'){
            $from_date = date('Y-m-d', strtotime("last week"));
        }else if($type == 'lastmonth'){
            $from_date = date('Y-m-d', strtotime("last month"));
        }else if($type == 'lastyear'){
            $from_date = date('Y-m-d', strtotime("last year"));
        }else if($type == 'custom'){
            $from_date = $array['from_date'];
            $to_date = $array['to_date'];
        }
        

        $data =  PaymentCollection::select('id','user_id','store_id','collection_amount','payment_type','bank_name','cheque_number','cheque_date','is_ledger_added')
                    ->where('store_id', $array['store_id'])
                    ->whereBetween('cheque_date', [$from_date, $to_date])
                    ->orderBy('id','desc')
                    ->take($take)->skip($skip)
                    ->get();
        // dd(DB::getQueryLog($data));
        // dd($data);
        return $data;
    }

    /**
     * This method is for PaymentCollection create
     * @param array $data
     * return in array format
     */
    public function create(array $data)
    {
        $collectedData = collect($data);

        $newEntry = new PaymentCollection;
        $newEntry->user_id = !empty($collectedData['user_id'])?$collectedData['user_id']:0;         
        $newEntry->store_id = $collectedData['store_id'];
        $newEntry->collection_amount = $collectedData['collection_amount'];
        $newEntry->bank_name = !empty($collectedData['bank_name'])?$collectedData['bank_name']:'';
        $newEntry->cheque_number = !empty($collectedData['cheque_number'])?$collectedData['cheque_number']:'';
        $newEntry->cheque_date = !empty($collectedData['cheque_date'])?$collectedData['cheque_date']:'';
        $newEntry->payment_type = $collectedData['payment_type'];
        // $newEntry->image = $collectedData['image'];
        /**
         * image uplaod section
         */
        // $upload_path = "uploads/PaymentCollection/";
        // $image = $collectedData['image'];
        // $imageName = time().".".$image->getClientOriginalName();
        // $image->move($upload_path, $imageName);
        // $uploadedImage = $imageName;
        // $newEntry->image= $upload_path.$uploadedImage;

        $newEntry->save();

        return $newEntry;
    }

    /**
     * This method is for PaymentCollection update
     * @param array $newDetails
     * return in array format
     */
    public function update($id, array $newDetails)
    {
        //
    }

    /**
     * This method is for  update PaymentCollection status
     * @param  $id
     *
     */
    public function toggle($id){
        $updatedEntry = PaymentCollection::findOrFail($id);

        $status = ( $updatedEntry->is_current == 1 ) ? 0 : 1;
        $updatedEntry->is_current = $status;
        $updatedEntry->save();

        return $updatedEntry;
    }

    /**
     * This method is for PaymentCollection delete
     * @param  $id
     *
     */
    public function delete($id)
    {
        PaymentCollection::destroy($id);
    }
}
