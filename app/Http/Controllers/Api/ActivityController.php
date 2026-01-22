<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    // private UserRepository $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function storeVisitStore(Request $request): JsonResponse
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'store_id' => ['required', 'integer', 'min:1', 'exists:stores,id'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d' , 'after:yesterday'],
            'start_time' => ['required' ,'date_format:H:i'],
            'start_location' => [ 'nullable'],
            'start_latitude' => ['required'],
            'start_longitude' => ['required']
        ]);
        
        $params = $request->except('_token');

        // dd($params);

        if(!$validator->fails()){
            $resp = $this->userRepository->storeVisitCreate($params);
            $resp_status = $resp['status'];
            
            if ($resp_status) {
                $store_vist = $resp['store_vist'];

                return response()->json(['error' => false, 'resp' => 'Store visit data created successfully', 'data' => $store_vist]);
            } else {
                $resp_message = $resp['message'];
                return response()->json(['error' => true, 'resp' => $resp_message ]);
            }


        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }

        
    }

    public function updateVisitLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visit_id' => ['required', 'integer', 'min:1'],            
            'latitude' => ['required'],
            'longitude' => ['required']
        ]);        
        $params = $request->except('_token');

        $dateTime = date('Y-m-d H:i:s');

        if(!$validator->fails()){
            $store_visit_details_last = DB::table('store_visit_details')->where('visit_id',$params['visit_id'])->orderBy('id','desc')->first();

            if(!empty($store_visit_details_last)){
                // dd($store_visit_details_last);
                $latitude1 = $store_visit_details_last->latitude;
                $longitude1 = $store_visit_details_last->longitude;

                $latitude2 = $params['latitude'];
                $longitude2 = $params['longitude'];

                // $GetDrivingDistance = GetDrivingDistance($latitude1,$longitude1,$latitude2,$longitude2);
                // $distance = $GetDrivingDistance['distance'];
                // $distance_value = $GetDrivingDistance['distance_value'];
                $distance = $distance_value = $sum_distance_val = '';

                DB::table('store_visit_details')->insert([
                    'visit_id' => $params['visit_id'],
                    'latitude' => $latitude2,
                    'longitude' => $longitude2,
                    'distance' => $distance,
                    'distance_value' => $distance_value,
                    'created_at'=>$dateTime,
                    'updated_at'=>$dateTime
                ]);

                // $sum_distance_val = DB::table('store_visit_details')->where('visit_id',$params['visit_id'])->sum('distance_value');

                // DB::table('store_visits')->where('id', $params['visit_id'])->update([
                //     'total_distance' => $sum_distance_val,
                //     'updated_at' => $dateTime
                // ]);


                return response()->json(['error'=>false,'message'=>"Location updated successfully. ".$distance." covered. Total distance covered ".$sum_distance_val." ", 'data'=> array() ]);
            }else{
                return response()->json(['error'=>true,'message'=>"please start visit first", 'data'=> (object)[] ]);
            }
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    }

    public function storeVisitEnd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visit_id' => ['required', 'integer', 'min:1'],
            'end_date' => ['required', 'date', 'after:yesterday'],
            'end_time' => ['required' ,'date_format:H:i'],
            'end_location' => [ 'nullable'],
            'end_latitude' => ['required'],
            'end_longitude' => ['required']
        ]);
        $dateTime = date('Y-m-d H:i:s');        
        $params = $request->except('_token','visit_id');
        // dd($params);
        $id = $request->visit_id;

        if(!$validator->fails()){
            $existStartedVisit = DB::table('store_visits')->where('id',$id)->where('end_date', '=', null)->where('end_time', '=', null)->first();

            if(!empty($existStartedVisit)){
                $store_visit_details_last = DB::table('store_visit_details')->where('visit_id',$id)->orderBy('id','desc')->first();

                if(!empty($store_visit_details_last)){
                    // dd($store_visit_details_last);
                    $latitude1 = $store_visit_details_last->latitude;
                    $longitude1 = $store_visit_details_last->longitude;

                    $latitude2 = $params['end_latitude'];
                    $longitude2 = $params['end_longitude'];

                    // $GetDrivingDistance = GetDrivingDistance($latitude1,$longitude1,$latitude2,$longitude2);
                    // $distance = $GetDrivingDistance['distance'];
                    // $distance_value = $GetDrivingDistance['distance_value'];
                    $distance = $distance_value = $sum_distance_val = $sum_distance_text = '';

                    DB::table('store_visit_details')->insert([
                        'visit_id' => $id,
                        'latitude' => $latitude2,
                        'longitude' => $longitude2,
                        'distance' => $distance,
                        'distance_value' => $distance_value,
                        'created_at'=>$dateTime,
                        'updated_at'=>$dateTime
                    ]);

                    // $sum_distance_val = DB::table('store_visit_details')->where('visit_id',$id)->sum('distance_value');
                    // $sum_distance_text = $sum_distance_val / 1000 .' km';

                    $upload_path = "public/uploads/store/";
                    if(!empty($collection['visit_image'])){
                        // dd($collection);
                        $image = $collection['visit_image'];           

                        $imageName = time().".".$image->getClientOriginalName();
                        $image->move($upload_path, $imageName);
                        $uploadedImage = $imageName;
                        $visit_image = $upload_path.$uploadedImage;
                    } else {
                        $visit_image = '';
                    }

                    DB::table('store_visits')->where('id', $id)->update([
                        'end_date' => $params['end_date'],
                        'end_time' => $params['end_time'],
                        'end_location' => $params['end_location'],
                        'end_latitude' => $params['end_latitude'],
                        'end_longitude' => $params['end_longitude'],                    
                        'total_distance' => $sum_distance_val,
                        'total_distance_text' => $sum_distance_text,
                        'updated_at' => $dateTime,
                        'visit_image' => $visit_image
                    ]);

                    $user_id = $existStartedVisit->user_id;

                    // $this->visitLedgerAdd($id,$user_id,$sum_distance_val,$params['end_date']);

                    return response()->json(['error'=>false,'message'=>"Store visit ended successfully", 'data'=> array() ]);
                }else{
                    return response()->json(['error'=>true,'message'=>"please start visit first", 'data'=> (object)[] ]);
                }
            } else {
                return response()->json(['error'=>true,'message'=>"Already ended", 'data'=> (object)[] ]);
            }

        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
       
    }

    private function visitLedgerAdd($id,$user_id,$sum_distance_val,$date)
    {
        
        $user = DB::table('users')->select('travelling_allowance')->where('id',$user_id)->first();

        $travelling_allowance = $user->travelling_allowance;  /* per km TA */
        $sum_distance_val = ($sum_distance_val / 1000); // converted to KM 
        $total_ta_amount = ($sum_distance_val*$travelling_allowance);

        $purpose = "travelling_allowance";
        $purpose_description = "Travelling Allowance";

        
        
        $last_closing_amount = !empty($ledger)?$ledger->last_closing_amount:0;
                
        $is_credit = 1;        
        $is_debit = 0;
        

        $transaction_id = "TA".date('YmdHis');

        /* Travelling Allowance insertion is now disable , now it's only enable as staff expense from master */
        /*DB::table('ledger')->insert([
            'user_type' => 'staff',
            'staff_id' => $user_id,
            'transaction_id' => $transaction_id,
            'transaction_amount' => $total_ta_amount,
            'is_credit' => $is_credit,
            'is_debit' => $is_debit,
            'entry_date' => $date,
            'purpose' => $purpose,
            'purpose_description' => $purpose_description
        ]);*/
        
        DB::table('store_visits')->where('id',$id)->update(['is_ledger_added' => $id, 'convenience_per_km' => $travelling_allowance, 'staff_convenience_amount' => $total_ta_amount, 'updated_at'=>date('Y-m-d H:i:s')]);


    }

   
}
