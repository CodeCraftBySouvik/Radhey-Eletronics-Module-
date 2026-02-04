<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\UserInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use \Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Models\Ledger;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    // private UserRepository $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(Request $request){
      try{

    
        $validator = Validator::make($request->all(), [
            'mobile'   => ['required', 'exists:users,mobile'],
            'password' => ['required'],
            'mac_id'   => ['required'],
            'latitude' => ['nullable'],
            'longitude'=> ['nullable'],
        ]);

         if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 400);
        }

         $user = User::where('mobile', $request->mobile)->first();

         /* User status check */
        if ($user->status != 1) {
            return response()->json([
                'error' => true,
                'message' => 'User is inactive'
            ], 403);
        }

         /*  Role access check */
        if ($user->type == 2 && !in_array($user->designation, [1, 4])) {
            return response()->json([
                'error' => true,
                'message' => 'You have no access to use this app'
            ], 403);
        }

         /* Password check */
        if ($request->mobile !== $user->mobile || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => true,
                'message' => 'Invalid mobile number or password'
            ], 401);
        }

         /* Single device login check */
        if (!empty($user->mac_id)) {
            return response()->json([
                'error' => true,
                'message' => 'You already logged in a device. Please log out first.'
            ], 403);
        }

          /* Update mac_id */
        $user->mac_id = $request->mac_id;
        $user->save();

         /*  Fetch user details */
        $userData = DB::table('users AS u')
            ->select(
                'u.id',
                'u.name',
                'u.mobile',
                'u.image',
                'u.designation',
                'designation.name AS designation_name',
                'u.type'
            )
            ->leftJoin('designation', 'designation.id', 'u.designation')
            ->where('u.id', $user->id)
            ->first();

        $userData->designation_name = $userData->designation_name ?? 'Partner';
        $attendance_id = 0;

        if ($user->type == 2 && in_array($user->designation, [1])) {

             if (empty($request->latitude) || empty($request->longitude)) {
            return response()->json([
                'error' => true,
                'message' => 'Latitude & Longitude are required'
            ], 400);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        // Check for ongoing attendance today
        $attendance = DB::table('user_attendances')
            ->where('user_id', $user->id)
            ->where('start_date', date('Y-m-d'))
            ->first();

        if ($attendance) {
            $attendance_id = $attendance->id;

            if ($attendance->status !== 'Leave') {
                DB::table('user_attendances')
                    ->where('id', $attendance_id)
                    ->update([
                        'status' => 'Present',
                        'updated_at' => now()
                    ]);
            }
            // Get last location
            $last_location = DB::table('attendance_locations')
                ->where('attendance_id', $attendance_id)
                ->orderBy('id', 'desc')
                ->first();

            $distance = 0;
            $distance_value = 0;
            if ($last_location) {
                $driving = GetDrivingDistance(
                    $last_location->latitude,
                    $last_location->longitude,
                    $latitude,
                    $longitude
                );

                $distance = $driving['distance'] ?? 0;
                $distance_value = $driving['distance_value'] ?? 0;
            }

            // Insert new location
            DB::table('attendance_locations')->insert([
                'user_id' => $user->id,
                'attendance_id' => $attendance_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'mac_id' => $request->mac_id,
                'distance' => $distance,
                'distance_value' => $distance_value,
                'entry_date' => date('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update total distance
            $total_distance_val = DB::table('attendance_locations')->where('attendance_id', $attendance_id)->sum('distance_value');
            $total_distance_text = ($total_distance_val / 1000) . ' km';

            DB::table('user_attendances')->where('id', $attendance_id)->update([
                'total_distance' => $total_distance_val,
                'total_distance_text' => $total_distance_text,
                'updated_at' => now(),
            ]);

        } else {
            // No attendance today, create one
            $attendance_id = DB::table('user_attendances')->insertGetId([
                'user_id' => $user->id,
                'status' => 'Present',
                'mac_id' => $request->mac_id,
                'start_date' => date('Y-m-d'),
                'start_time' => date('H:i'),
                'start_latitude' => $latitude,
                'start_longitude' => $longitude,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert first location
            DB::table('attendance_locations')->insert([
                'user_id' => $user->id,
                'attendance_id' => $attendance_id,
                'mac_id' => $request->mac_id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'entry_date' => date('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $userData->attendance_id = $attendance_id ?? 0;
      }

       return response()->json([
            'error' => false,
            'message' => 'Logged in successfully',
            'data' => $userData,
            // 'token' => $user->createToken('mobile')->plainTextToken, // Optional
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => true,
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }

    }
    
    // public function logincheck(Request $request) {
    //     // $response = $this->userRepository->otpGenerate($request->mobile);
    //     $validator = Validator::make($request->all(), [
    //         'mobile' => ['required', 'exists:users,mobile']            
    //     ]);

    //     if($validator->fails()){
    //         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    //     } else {
    //         $params = $request->except('_token');
    //         $mobile = $params['mobile'];

    //         $userExists = User::where('mobile', $mobile)->first();
    //         if ($userExists) {
    //             if($userExists->status == 1){                
    //                 if($userExists->type == 2 && (!in_array($userExists->designation, [1,4])) ){
    //                     $resp = [
    //                         'error' => true, 
    //                         'message' => 'You have no access to use this app'
    //                     ];
    //                 } else {
                        
    //                     if(!empty($userExists->mac_id)){
    //                         $resp = [
    //                             'error' => true, 
    //                             'message' => 'You already logged in a device. Please log out first.'
    //                         ];
    //                     }else{
    //                         $otp = 1234;
    //                         // $otp = mt_rand(0, 10000);
    //                         $userExists->otp = $otp;
    //                         // sms gateway
    //                         $userExists->save();

    //                         $designation = "Partner";
    //                         if($userExists->designation == 1){
    //                             $designation = "Salesman";
    //                         } else if ($userExists->designation == 4){
    //                             $designation = "Godown Manager";
    //                         }

    //                         $resp = [
    //                             'error' => false, 
    //                             'message' => 'OTP generated successfully, you logged in as '.$designation.' ', 
    //                             'data' => [
    //                                 'id' => $userExists->id,
    //                                 'otp' => $otp
    //                             ]
    //                         ];
    //                     }
                        
    //                 }            
                    
    //             } else {
    //                 $resp = [
    //                     'error' => true, 
    //                     'message' => 'User is inactive'
    //                 ];
    //             }                

    //         } else {
    //             $resp = [
    //                 'error' => true, 
    //                 'message' => 'User does not exist'
    //             ];
    //         }
    //         return response()->json($resp);
    //     }
        
    // }

    // public function otpcheck(Request $request) {
    //     // $response = $this->userRepository->otpcheck($request->mobile, $request->otp, $request->lat, $request->long);
    //     $validator = Validator::make($request->all(), [
    //         'mobile' => ['required', 'exists:users,mobile'],
    //         'otp' => ['required'],
    //         'mac_id' => ['required'],
    //         'latitude' => ['nullable'],
    //         'longitude' => ['nullable']
    //     ]);

    //     if($validator->fails()){
    //         return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    //     } else {
    //         $params = $request->except('_token');
    //         $mobile = $params['mobile'];
    //         $otp = $params['otp'];
    //         $userExists = User::where('mobile', $mobile)->where('otp', $otp)->first();
    //         if ($userExists) {            

    //             $id = $userExists->id;
                
    //             if(!empty($userExists->mac_id)){
    //                 $resp = [
    //                     'error' => true,
    //                     'message' => 'You already logged in a device. Please log out first.'
    //                 ];
    //             }else{
    //                 $user = DB::table('users AS u')->select('u.id','u.name','u.whatsapp_no','u.mobile','u.image','u.designation','designation.name AS designation_name','u.type')->leftJoin('designation', 'designation.id','u.designation')->where('u.id',$id)->first();
                
    //                 $total_payment_collection = $total_commission_earned = 0;
    //                 if($userExists->type == 2){
    //                     $total_payment_collection = DB::table('payment_collections')->where('user_id',$id)->where('is_ledger_added', 1)->sum('collection_amount');
        
    //                     $total_commission_earned = DB::table('ledger')->where('user_type','staff')->where('staff_id',$id)->where('purpose', 'sales_order_payment_commission')->sum('transaction_amount');                
    //                 }    
                    
    //                 $user->total_payment_collection = $total_payment_collection;   
    //                 $user->total_commission_earned = $total_commission_earned; 
        
    //                 $user->designation_name = !empty($user->designation_name)?$user->designation_name:'Partner';
    //                 $user->attendance_id = 0;

    //                 // /* Make OTP blank */
    //                 User::where('id',$id)->update(['mac_id'=> $params['mac_id'] ]);
    //                 /* Staff Attendance With Location */
    //                 if($userExists->type == 2 && (in_array($userExists->designation, [1]))) {                         
    //                     if(empty($request->latitude)){
    //                         return response()->json([
    //                             'status' => 400,
    //                             'message' => 'Latitude is required'
    //                         ], 400);
    //                     }
    //                     if(empty($request->longitude)){
    //                         return response()->json([
    //                             'status' => 400,
    //                             'message' => 'Longitude is required'
    //                         ], 400);
    //                     }

    //                     if(($request->latitude == '0.0') || ($request->longitude == '0.0')){
    //                         return response()->json(['error' => true, 'status' => 400, 'message' => "Invalid latitude & longitude" ], 400);
    //                     }

    //                     $latitude = $request->latitude;
    //                     $longitude = $request->longitude;                        
    //                     # Check exist previous incomplete or not ledgered attendance
    //                     $checkExistingAttendance = DB::table('user_attendances')->where('user_id',$id)->where('start_date', '!=', date('Y-m-d'))->where('is_ledger_added', 0)->orderBy('id','desc')->first();

    //                     if(!empty($checkExistingAttendance)){
    //                         $this->visitLedgerAdd($checkExistingAttendance->id,$id,$checkExistingAttendance->total_distance,$checkExistingAttendance->start_date);
                            
    //                     }
    //                     # Check exist today ongoing attendance
    //                     $checkExistOngoingAttendance = DB::table('user_attendances')->where('user_id',$id)->where('start_date', date('Y-m-d'))->first();
    //                     if(!empty($checkExistOngoingAttendance)){
    //                         $attendance_id = $checkExistOngoingAttendance->id;
    //                         $attendance_locations_last = DB::table('attendance_locations')->where('attendance_id',$attendance_id)->orderBy('id','desc')->first();

    //                         $latitude1 = $attendance_locations_last->latitude;
    //                         $longitude1 = $attendance_locations_last->longitude;

    //                         $latitude2 = $latitude;
    //                         $longitude2 = $longitude;

    //                         $GetDrivingDistance = GetDrivingDistance($latitude1,$longitude1,$latitude2,$longitude2);

    //                         $distance = $GetDrivingDistance['distance'];
    //                         $distance_value = $GetDrivingDistance['distance_value'];


    //                         DB::table('attendance_locations')->insert([
    //                             'user_id' => $id,
    //                             'attendance_id' => $attendance_id,
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude,
    //                             'mac_id' => $params['mac_id'],
    //                             'distance' => $distance,
    //                             'distance_value' => $distance_value,
    //                             'entry_date' => date('Y-m-d'),
    //                             'created_at' => date('Y-m-d H:i:s'),
    //                             'updated_at' => date('Y-m-d H:i:s')
    //                         ]);

    //                         $sum_distance_val = DB::table('attendance_locations')->where('attendance_id',$attendance_id)->sum('distance_value');

    //                         $sum_distance_text = $sum_distance_val / 1000 .' km';

    //                         DB::table('user_attendances')->where('id', $attendance_id)->update([
    //                             'is_ledger_added' => 0,
    //                             'total_distance' => $sum_distance_val,
    //                             'total_distance_text' => $sum_distance_text,
    //                             'updated_at' => date('Y-m-d H:i:s')
    //                         ]);

    //                         # Delete today's existing ledger entry because location started again
    //                         DB::table('ledger')->where('transaction_id', $checkExistOngoingAttendance->transaction_id)->delete();



    //                     }else{
    //                         $attendance_id = DB::table('user_attendances')->insertGetId([
    //                             'user_id' => $id,
    //                             'mac_id' => $params['mac_id'],
    //                             'start_date' => date('Y-m-d'),
    //                             'start_time' => date('H:i'),
    //                             'start_latitude' => $latitude,
    //                             'start_longitude' => $longitude,
    //                         ]);   
    //                         DB::table('attendance_locations')->insert([
    //                             'user_id' => $id,
    //                             'attendance_id' => $attendance_id,
    //                             'mac_id' => $params['mac_id'],
    //                             'latitude' => $latitude,
    //                             'longitude' => $longitude,
    //                             'entry_date' => date('Y-m-d'),
    //                             'created_at' => date('Y-m-d H:i:s'),
    //                             'updated_at' => date('Y-m-d H:i:s')
    //                         ]);                           
    //                     }

    //                     $user->attendance_id = $attendance_id;
                                              
    //                 }
        
    //                 $resp = [
    //                     'error' => false, 
    //                     'message' => 'OTP matched. Logged in successfully', 
    //                     'data' => $user
    //                 ];
    //             }
                
    //         } else {
    //             $resp = [
    //                 'error' => true, 
    //                 'message' => 'You have entered an wrong otp. Please try with the correct one.'
    //             ];
    //         }
    //     }
    //     return response()->json($resp);
    // }

    public function location_update(Request $request)
    {
        # location update ...
        
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'mac_id' => ['required']
        ]);
        
        $params = $request->except('_token');

        if(!$validator->fails()){
            if(($params['latitude'] == '0.0') || ($params['longitude'] == '0.0')){
                return response()->json(['error' => true, 'status' => 400, 'message' => "Invalid latitude & longitude" ], 400);
            }
            $entry_date = date('Y-m-d');
            $user_id = $params['user_id'];
            $check_exist_attendance = DB::table('user_attendances')->where('user_id',$user_id)->where('start_date',$entry_date)->first();

            if(!empty($check_exist_attendance)){
                $attendance_id = $check_exist_attendance->id;

                $attendance_locations_last = DB::table('attendance_locations')->where('attendance_id',$attendance_id)->orderBy('id','desc')->first();

                $latitude1 = $attendance_locations_last->latitude;
                $longitude1 = $attendance_locations_last->longitude;

                $latitude2 = $params['latitude'];
                $longitude2 = $params['longitude'];

                $GetDrivingDistance = GetDrivingDistance($latitude1,$longitude1,$latitude2,$longitude2);

                $distance = $GetDrivingDistance['distance'];
                $distance_value = $GetDrivingDistance['distance_value'];


                DB::table('attendance_locations')->insert([
                    'user_id' => $user_id,
                    'attendance_id' => $attendance_id,
                    'latitude' => $params['latitude'],
                    'longitude' => $params['longitude'],
                    'mac_id' => $params['mac_id'],
                    'distance' => $distance,
                    'distance_value' => $distance_value,
                    'entry_date' => $entry_date,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $sum_distance_val = DB::table('attendance_locations')->where('attendance_id',$attendance_id)->sum('distance_value');

                $sum_distance_text = $sum_distance_val / 1000 .' km';

                DB::table('user_attendances')->where('id', $attendance_id)->update([
                    'end_date' => date('Y-m-d'),
                    'end_time' => date('H:i'),
                    'end_latitude' => $params['latitude'],
                    'end_longitude' => $params['longitude'],
                    'total_distance' => $sum_distance_val,
                    'total_distance_text' => $sum_distance_text,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                return response()->json(['error' => false, 'resp' => "Your current location saved successfully", 'data' => array(
                        'latitude'=>$params['latitude'],
                        'longitude'=>$params['longitude'],
                        'mac_id' => $params['mac_id'],
                        'total_distance' => $sum_distance_text,
                    ) 
                ]);


            } else {
                return response()->json(['error' => true, 'resp' => "Your attendance is not started. Please login to app with your location" ]);
            }

        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    }

    public function logout(Request $request)
    {
        # logout manually ...

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        $params = $request->except('_token');
        if(!$validator->fails()){

            $user_id = $params['user_id'];
            User::where('id',$user_id)->update(['mac_id' => null]);

            /* Ledger Entry */
            $user_attendances = DB::table('user_attendances')->where('user_id',$user_id)->where('is_ledger_added', 0)->get();
            if(!empty($user_attendances)){
                foreach($user_attendances as $item){
                    $attendance_locations_last = DB::table('attendance_locations')->where('attendance_id',$item->id)->orderBy('id','desc')->first();
                    DB::table('user_attendances')->where('id',$item->id)->update([
                        'end_date' => $item->start_date,
                        'end_time' => date('H:i'),
                        'end_latitude' => $attendance_locations_last->latitude,
                        'end_longitude' => $attendance_locations_last->longitude
                    ]);

                    $this->visitLedgerAdd($item->id,$user_id,$item->total_distance,$item->start_date);
                }
            }


                        
            $response = [
                'error' => false, 
                'message' => 'User Logged Out Successfully', 
                'data' => array()
            ];

            return response()->json($response);

        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'error' => true, 
                    'message' => 'Validation', 
                    'data' => $validator->errors()->first()
                ]
            );
        }


        
    }

    public function checkUserMacId(Request $request)
    {
        # check user with mac id exists or not...

        $validator = Validator::make($request->all(),[
            'user_id' => ['required'],
            'mac_id' => ['required']
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $user_id = $params['user_id'];
            $mac_id = $params['mac_id'];
            $data = User::where('id',$user_id)->first();

            if(!empty($data)){

                if($data->designation == 1){
                    $checkExistingAttendance = DB::table('user_attendances')->where('user_id',$user_id)->where('start_date', '=', date('Y-m-d'))->first();

                    if(empty($checkExistingAttendance)){
                        return response()->json(['error' => false, 'resp' => "You haven't started your day. Please relogin" ]);
                    }
                }

                if($data->mac_id == $mac_id){
                    return response()->json(['error' => false, 'resp' => "Same mac id exist" ]);
                }else{
                    if(!empty($data->mac_id)){
                        return response()->json(['error' => true, 'resp' => "Exising mac id is different ".$data->mac_id." " ]);
                    }else{
                        return response()->json(['error' => true, 'resp' => "No mac id found" ]);
                    }
                    
                }
                
            } else {
                return response()->json(['error' => true, 'resp' => "No user found " ]);
            }
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
        }
    }

    private function visitLedgerAdd($id,$user_id,$sum_distance_val,$entry_date)
    {
        # attendance id is $id.
        $user = DB::table('users')->select('travelling_allowance')->where('id',$user_id)->first();

        $travelling_allowance = $user->travelling_allowance;  /* per km TA */
        $sum_distance_val = ($sum_distance_val / 1000); // converted to KM 
        $total_ta_amount = ($sum_distance_val*$travelling_allowance);

        $purpose = "travelling_allowance";
        $purpose_description = "Travelling Allowance";

        $is_credit = 1;        
        $is_debit = 0;       

        $transaction_id = "TA".$user_id."".date('Ymd', strtotime($entry_date));

        /* Travelling Allowance insertion is now disable , now it's only enable as staff expense from master */
        /*Ledger::insert([
            'user_type' => 'staff',
            'staff_id' => $user_id,
            'transaction_id' => $transaction_id,
            'transaction_amount' => $total_ta_amount,
            'is_credit' => $is_credit,
            'is_debit' => $is_debit,
            'entry_date' => $entry_date,
            'purpose' => $purpose,
            'purpose_description' => $purpose_description
        ]);*/
        
        DB::table('user_attendances')->where('id',$id)->update(['is_ledger_added' => 1, 'transaction_id' => $transaction_id, 'convenience_per_km' => $travelling_allowance, 'staff_convenience_amount' => $total_ta_amount, 'updated_at'=>date('Y-m-d H:i:s')]);


    }
    
}
