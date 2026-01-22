<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\Changelog;
use App\Models\Packingslip;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\StaffCommision;
use App\Models\Ledger;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Models\StockBox;
use App\Models\StockLog;
use App\Models\WhatsAppInvoice;
use App\Models\PurchaseOrderProduct;
use Carbon\Carbon;

// $ip = $_SERVER['REMOTE_ADDR'];

// send mail helper
function SendMail($data)
{
    // mail log
    $newMail = new \App\Models\MailLog();
    $newMail->from = 'onenesstechsolution@gmail.com';
    $newMail->to = $data['email'];
    $newMail->subject = $data['subject'];
    $newMail->blade_file = $data['blade_file'];
    $newMail->payload = json_encode($data);
    $newMail->save();

    // send mail
    Mail::send($data['blade_file'], $data, function ($message) use ($data) {
        $message->to($data['email'], $data['name'])->subject($data['subject'])->from('onenesstechsolution@gmail.com', env('APP_NAME'));
    });
}

// multi-dimensional in_array
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) return true;
    }
    return false;
}

function groupConcatNames($tableName,$columnName,array $array){
    // echo 'Hi'; exit;
    $data = DB::table($tableName)->selectRaw("GROUP_CONCAT(".$columnName.") AS names")->whereIn('id', $array)->get();

    return str_replace(","," , ",$data[0]->names);
    //  $data[0]->names;
}

function productUnit($product_id)
{
    $product = \App\Models\Product::find($product_id);
    return $product->unit_value.' '.$product->unit_type.' ('.$product->weight_value.' '.$product->weight_type.')';
}

function getProductIdFromBarcode($barcode_no)
{
    $data = DB::table('purchase_order_boxes')->where('barcode_no',$barcode_no)->first();
    $product_id = !empty($data)?$data->product_id:0;
    return $product_id;
}


function getRandString($n=10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
  
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
  
    return $randomString;
}

function checkStockPO($product_id,$qty){
    $checkStock = DB::table('stock_boxes')->where('is_scanned',0)->where('product_id',$product_id)->count();
    
    if(!empty($checkStock)){
        
        $pieces = DB::table('stock_boxes')->where('is_scanned',0)->where('product_id',$product_id)->sum('pcs');
        return array('status'=>'In Stock','stock'=>$checkStock,'pieces'=>$pieces,'is_stock'=>1);
        // echo 'In stock';
        
    }else{
        
        return array('status'=>'Out of Stock','pieces'=>0,'stock'=>0,'is_stock'=>0);
    }
    
}

function barcodeGen(){
    $length = 12;    
    $min = str_repeat(0, $length-1) . 1;
    $max = str_repeat(9, $length);
    $barcode_no =  mt_rand($min, $max);   

    $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
    $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

    $code_html = $generator->getBarcode($barcode_no, $generator::TYPE_CODE_128);

    $code_base64_img = base64_encode($generatorPNG->getBarcode($barcode_no, $generatorPNG::TYPE_CODE_128));

    return array('barcode_no'=>$barcode_no,'code_html'=>$code_html,'code_base64_img'=>$code_base64_img);

    
}

// function getBarcodeDetails($barcode_no){
//     $data = DB::table('purchase_order_boxes')
//     ->join('products', 'purchase_order_boxes.product_id', '=', 'products.id')
//     ->where('purchase_order_boxes.barcode_no', $barcode_no)
//     ->select(
//         'purchase_order_boxes.code_html',
//         'purchase_order_boxes.code_base64_img',
//         'purchase_order_boxes.po_weight_val',
//         'purchase_order_boxes.scanned_weight_val',
//         'purchase_order_boxes.pcs',
//         'purchase_order_boxes.product_id',
//         'products.hsn_code', // Fetch existing field
//         'products.sell_price', // Use as a reference price if needed
//         'products.threshold_price' // Use if required for calculations
//     )
//     ->first();

//     return array(
//         'code_html'=>$data->code_html,
//         'code_base64_img'=>$data->code_base64_img,
//         'po_weight_val'=>$data->po_weight_val,
//         'scanned_weight_val'=>$data->scanned_weight_val,
//         'pcs'=>$data->pcs,
//         'product_id'=>$data->product_id
//     );
// }

function getBarcodeDetails($barcode_no){
    $data = DB::table('purchase_order_boxes')
        ->join('products', 'purchase_order_boxes.product_id', '=', 'products.id')
        ->where('purchase_order_boxes.barcode_no', $barcode_no)
        ->select(
            'purchase_order_boxes.code_html',
            'purchase_order_boxes.code_base64_img',
            'purchase_order_boxes.po_weight_val',
            'purchase_order_boxes.scanned_weight_val',
            'purchase_order_boxes.pcs',
            'purchase_order_boxes.product_id',
            'purchase_order_boxes.purchase_order_id',
            'products.hsn_code', 
            'products.sell_price', // Assuming this is the sell price per carton
            'products.threshold_price' // Optional, if threshold logic is needed
        )
        ->first();
        $update_piece_price = 0;
        if($data){
            $data->product_id;
            $data->purchase_order_id;
            $GetProductPrice =PurchaseOrderProduct::select('unit_price')->where('purchase_order_id', $data->purchase_order_id)->where('product_id', $data->product_id)->first();
            $update_piece_price = $GetProductPrice->unit_price;
        }
    // If data is null, return empty values to avoid errors
    if (!$data) {
        return [
            'code_html' => '',
            'code_base64_img' => '',
            'po_weight_val' => 0,
            'scanned_weight_val' => 0,
            'pcs' => 0,
            'product_id' => null,
            'hsn_code' => '',
            'price_per_carton' => 0, // Default to 0
            'piece_price' => 0 // Default to 0
        ];
    }
    return [
        'code_html' => $data->code_html ?? '',
        'code_base64_img' => $data->code_base64_img ?? '',
        'po_weight_val' => $data->po_weight_val ?? 0,
        'scanned_weight_val' => $data->scanned_weight_val ?? 0,
        'pcs' => $data->pcs ?? 0,
        'product_id' => $data->product_id ?? null,
        'hsn_code' => $data->hsn_code ?? '',
        'price_per_carton' => $update_piece_price ?? 0, // Assuming sell_price represents price per carton
        'piece_price' => $data->pcs > 0 ? $update_piece_price / $data->pcs : 0 // Calculate price per piece
    ];
}



function getBarcodeDetailsStock($barcode_no){
    $data = DB::table('stock_boxes')->where('barcode_no',$barcode_no)->first();
    if(!empty($data)){
        return array(
            'product_id'=>$data->product_id,
            'code_html'=>$data->code_html,
            'code_base64_img'=>$data->code_base64_img,
            'stock_in_weight_val'=>$data->stock_in_weight_val,
            'stock_out_weight_val'=>$data->stock_out_weight_val,
            'pcs'=>$data->pcs
        );
    } else {
        // return false;
        return array();
    }
    
}

function getBarcodeDetailsReturns($barcode_no){
    $data = DB::table('return_boxes')->where('barcode_no',$barcode_no)->first();
    return array(
        'code_html'=>$data->code_html,
        'code_base64_img'=>$data->code_base64_img,
        'pcs'=>$data->pcs,
        'product_id'=>$data->product_id
    );
}

function getOrderProductDetails($order_id=0,$product_id=0){
    if(!empty($order_id) && !empty($product_id)){
        $data = DB::table('order_products')->where('order_id',$order_id)->where('product_id',$product_id)->first();

        return $data;
    }
}

function getAmountAlphabetically($amount){
    $number = $amount;
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $hundred = null;
    $digits_1 = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        '0' => '', 
        '1' => 'one', 
        '2' => 'two',
        '3' => 'three', 
        '4' => 'four', 
        '5' => 'five', 
        '6' => 'six',
        '7' => 'seven', 
        '8' => 'eight', 
        '9' => 'nine',
        '10' => 'ten', 
        '11' => 'eleven', 
        '12' => 'twelve',
        '13' => 'thirteen', 
        '14' => 'fourteen',
        '15' => 'fifteen', 
        '16' => 'sixteen', 
        '17' => 'seventeen',
        '18' => 'eighteen', 
        '19' =>'nineteen', 
        '20' => 'twenty',
        '30' => 'thirty', 
        '40' => 'forty', 
        '50' => 'fifty',
        '60' => 'sixty', 
        '70' => 'seventy',
        '80' => 'eighty', 
        '90' => 'ninety'
    );
    $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
        $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
        } else $str[] = null;
    }
    $str = array_reverse($str);
    $result = implode('', $str);
    $points = ($point) ? "" . $words[$point / 10] . " " . $words[$point = $point % 10] : 'Zero';
    return  ucwords($result) . " Rupees  and " . $points . " Paise";
}

function getOrderDetails($order_id){
    $order = DB::table('orders')->find($order_id);
    return $order;
}

function getPercentageVal($percent,$number){
    $percent = floatval($percent);
    $number = floatval($number);
    return ($percent / 100) * $number;
}

function getMonthName(){
    $months = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November',  '12'=>'December' );

    return $months;
    
}

function GetDrivingDistance($lat1, $lon1, $lat2, $lon2){
    // $oldKey = "AIzaSyDPuZ9AcP4PHUBgbUsT6PdCRUUkyczJ66I";
    // $key = "AIzaSyDegpPMIh4JJgSPtZwE6cfTjXSQiSYOdc4";
    $settings = DB::table('settings')->find(1);
    $google_api_key = $settings->google_api_key;

    if($lat2!=0 && $lon2!=0){
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $lon1 . "&destinations=" . $lat2 . "," . $lon2 . "&mode=driving&key=".$google_api_key;
           
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);

        //echo $response;
        curl_close($ch);
        $response_a = json_decode($response, true);

        // return $response_a;

       if (!empty($response_a['rows']) &&
    isset($response_a['rows'][0]['elements'][0]['distance']['text']) &&
    isset($response_a['rows'][0]['elements'][0]['distance']['value']) &&
    isset($response_a['rows'][0]['elements'][0]['duration']['value'])) {
            // dd($response_a['rows']);
            $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
            // return $dist;
            $distance_value = $response_a['rows'][0]['elements'][0]['distance']['value'];

            $total_time = $response_a['rows'][0]['elements'][0]['duration']['value'];

            $time = $total_time / 60;

            return array('distance' => $dist, 'distance_value' => $distance_value , 'time' => $time);
        }else{
            return array('distance' => '0', 'distance_value' => '0', 'time' => '0');
        }

    }else{
        return array('distance' => '0', 'distance_value' => '0',  'time' => '0');
    }
}


function GetDrivingDistanceTest($lat1, $lon1, $lat2, $lon2){
    $oldKey = "AIzaSyDPuZ9AcP4PHUBgbUsT6PdCRUUkyczJ66I";
    $key = "AIzaSyDegpPMIh4JJgSPtZwE6cfTjXSQiSYOdc4";
    $settings = DB::table('settings')->find(1);
    $google_api_key = $settings->google_api_key;
    // $abhinavKey = "AIzaSyAkwlSsaYwOB0T79ZgKI7_vgQNbRxzD1xc";
    if($lat2!=0 && $lon2!=0){
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat1 . "," . $lon1 . "&destinations=" . $lat2 . "," . $lon2 . "&mode=driving&key=".$google_api_key;
           
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);

        //echo $response;
        curl_close($ch);
        $response_a = json_decode($response, true);

        return $response_a;
        if(!empty($response_a['rows'])){
            $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];

            // return $dist;
            $distance_value = $response_a['rows'][0]['elements'][0]['distance']['value'];

            $total_time = $response_a['rows'][0]['elements'][0]['duration']['value'];

            $time = $total_time / 60;

            return array('distance' => $dist, 'distance_value' => $distance_value , 'time' => $time);
        }else{
            return array('distance' => '0', 'distance_value' => '0', 'time' => '0');
        }

        
    }else{
        return array('distance' => '0',  'distance_value' => '0', 'time' => '0');
    }
}

// function updateProductSellPrice($price,$cost_price,$product_id){
//     $settings = DB::table('settings')->find(1);
//     $product_sales_price_threshold_percentage =$settings? $settings->product_sales_price_threshold_percentage : "";

//     $product_threshold_sell_price = getPercentageVal($product_sales_price_threshold_percentage,$cost_price);
//     $new_sell_price = ($cost_price + $product_threshold_sell_price);
//     DB::table('products')->where('id',$product_id)->update([
//         'threshold_price' => $price,
//         'product_sales_price_threshold_percentage' => $product_sales_price_threshold_percentage,
//         'sell_price' => $new_sell_price,
//         'cost_price' => $cost_price
//     ]);
// }

function updateProductSellPrice($price, $cost_price, $product_id) {
    $settings = DB::table('settings')->find(1);
    $product_sales_price_threshold_percentage = $settings ? $settings->product_sales_price_threshold_percentage : 0;  // Default to 0 if empty

    // Ensure threshold percentage is valid
    if ($product_sales_price_threshold_percentage <= 0) {
        return $cost_price; // If no threshold percentage is provided, return cost price as sell price
    }

    // Calculate the product threshold sell price
    $product_threshold_sell_price = getPercentageVal($product_sales_price_threshold_percentage, $cost_price);
    $new_sell_price = ($cost_price + $product_threshold_sell_price);
    // Update the product record with calculated values
    DB::table('products')->where('id', $product_id)->update([
        'threshold_price' => $price,
        'product_sales_price_threshold_percentage' => $product_sales_price_threshold_percentage,
        'sell_price' => $new_sell_price,
        'cost_price' => $cost_price
    ]);

    // Return the new sell price
    return $new_sell_price;
}



function getCrDr($amount){
    if($amount > 0){
        return "Cr"; # if postive +
    } else if($amount < 0) {
        return "Dr"; # if negative -
    } else {
        return "";
    }
}

function replaceMinusSign($number){
    return str_replace("-","",$number);
}

function getDaysFromDateRange($start, $end, $format = 'Y-m-d') {
    
    $earlier = new DateTime(date('Y-m-d', strtotime($start)));
    $later = new DateTime(date('Y-m-d', strtotime($end)));
    // echo $start;
    // echo $end;
    $diff = $earlier->diff($later);
    // echo $diff->days; 
    return $diff->days;

}

function getGSTAmount($cost_price,$gst_val){

    /*
    
        Remove GST
        GST Amount = Original Cost – (Original Cost * (100 / (100 + GST% ) ) )
        Net Price = Original Cost – GST Amount

        GST Amount = 150 - (150 * ( 100 / ( 100 + 18% ) )) = 150 - ( 150 * ( 100 / 118 ) ) = 22.88
        Net Price = 150 - 22.88 = 127.12

    */

    $gst_amount = $cost_price - ( $cost_price * ( 100 / ( 100 + (getPercentageVal($gst_val,100)) ) ) );
    $net_price = ($cost_price - $gst_amount);

    return array('gst_amount' => $gst_amount , 'net_price' => $net_price);
}

function userAccesses($designation_id,$role_id){
    $data = DB::table('user_roles')->where('designation_id',$designation_id)->where('role_id',$role_id)->first();

    if(!empty($data)){
        return true;
    } else {
        return false;
    }
}

function getSingleAttributeTable($tableName,$id,$field){
    $data = DB::table($tableName)->select($field)->where('id',$id)->first();
    return !empty($data)?$data->$field:'';
}


function isBulkScanned($purchase_order_id,$product_id){
    $data = DB::table('purchase_order_boxes')->where('purchase_order_id',$purchase_order_id)->where('product_id',$product_id)->where('is_bulk_scanned', 1)->first();

    if(!empty($data)){
        return true;
    } else {
        return false;
    }
}

function genAutoIncreNoInv($table='invoice', $length=10,$type='g'){
    $val = 1;    
    $data = DB::table($table)->select('id')->orderBy('id','desc')->first();
    if(empty($data)){
        $val = 1;
    } else {
        $val = $data->id + 1;
    }
    
    $number = str_pad($val,$length,"0",STR_PAD_LEFT);
    return $number;
}

function updatelocationattendance($attendance_id,$latitude,$longitude,$store_id){

    $attendance_locations_last = DB::table('attendance_locations')->where('attendance_id',$attendance_id)->orderBy('id','desc')->first();

    $latitude1 = $attendance_locations_last->latitude;
    $longitude1 = $attendance_locations_last->longitude;
    $mac_id = $attendance_locations_last->mac_id;
    $user_id = $attendance_locations_last->user_id;

    $latitude2 = $latitude;
    $longitude2 = $longitude;

    $GetDrivingDistance = GetDrivingDistance($latitude1,$longitude1,$latitude2,$longitude2);

    $distance = $GetDrivingDistance['distance'];
    $distance_value = $GetDrivingDistance['distance_value'];

    DB::table('attendance_locations')->insert([
        'user_id' => $user_id,
        'attendance_id' => $attendance_id,
        'latitude' => $latitude2,
        'longitude' => $longitude2,
        'store_id' => $store_id,
        'mac_id' => $mac_id,
        'distance' => $distance,
        'distance_value' => $distance_value,
        'entry_date' => date('Y-m-d'),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    $sum_distance_val = DB::table('attendance_locations')->where('attendance_id',$attendance_id)->sum('distance_value');
    $sum_distance_text = $sum_distance_val / 1000 .' km';

    DB::table('user_attendances')->where('id', $attendance_id)->update([
        'end_date' => date('Y-m-d'),
        'end_time' => date('H:i'),
        'end_latitude' => $latitude2,
        'end_longitude' => $longitude2,
        'total_distance' => $sum_distance_val,
        'total_distance_text' => $sum_distance_text,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

function getPODetails($id,$product_id){
    $data = DB::table('purchase_order_products')->where('purchase_order_id',$id)->where('product_id',$product_id)->first();

    return $data;
}

function getCountPOGRN($id,$product_id){
    $data = DB::table('purchase_order_boxes')->where('purchase_order_id',$id)->where('product_id',$product_id)->where('is_scanned', 1)->where('is_archived', 0)->count();

    return $data;
}

function isBulkScannedReturn($return_id,$product_id){
    $data = DB::table('return_boxes')->where('return_id',$return_id)->where('product_id',$product_id)->where('is_bulk_scanned', 1)->first();

    if(!empty($data)){
        return true;
    } else {
        return false;
    }
}

function getCountReturnGoodsIn($id,$product_id){
    $data = DB::table('return_boxes')->where('return_id',$id)->where('product_id',$product_id)->where('is_scanned', 1)->count();

    return $data;
}

function genAutoIncreNoBarcode($product_id,$year){
    $val = 1;    
    $data = DB::table('purchase_order_boxes')->where('product_id',$product_id)->whereRaw("DATE_FORMAT(created_at, '%Y') = '".$year."'")->count();

    if(!empty($data)){
        $val = ($data + 1);
    }

    // dd($data);
    $prefix = $product_id.''.$year.'';
    $suffix = str_pad($val,7,"0",STR_PAD_LEFT);
    $number = $prefix.''.$suffix;
    $barcode_no = $number;
    $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
    $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

    $code_html = $generator->getBarcode($barcode_no, $generator::TYPE_CODE_128);

    $code_base64_img = base64_encode($generatorPNG->getBarcode($barcode_no, $generatorPNG::TYPE_CODE_128));

    return array('barcode_no'=>$barcode_no,'code_html'=>$code_html,'code_base64_img'=>$code_base64_img);
    
}

function genAutoIncreNoBarcodeReturn($product_id,$year){
    $val = 1;    
    $data = DB::table('return_boxes')->where('product_id',$product_id)->whereRaw("DATE_FORMAT(created_at, '%Y') = '".$year."'")->count();

    if(!empty($data)){
        $val = ($data + 1);
    }

    // dd($data);
    $prefix = 'RE'.$product_id.''.$year.'';
    $suffix = str_pad($val,7,"0",STR_PAD_LEFT);
    $number = $prefix.''.$suffix;
    $barcode_no = $number;
    $generator = new Picqer\Barcode\BarcodeGeneratorHTML();
    $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

    $code_html = $generator->getBarcode($barcode_no, $generator::TYPE_CODE_128);

    $code_base64_img = base64_encode($generatorPNG->getBarcode($barcode_no, $generatorPNG::TYPE_CODE_128));

    return array('barcode_no'=>$barcode_no,'code_html'=>$code_html,'code_base64_img'=>$code_base64_img);
    
}

function getReturnDetails($id,$product_id){
    $data = DB::table('return_products')->where('return_id',$id)->where('product_id',$product_id)->first();

    return $data;
}

function changelogentry($doneby,$purpose,$data_details){    
    $insertData = array(
        'doneby' => $doneby,
        'purpose' => $purpose,
        'data_details' => $data_details,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    );
    Changelog::insert($insertData);
}

function get_packing_slip($slip_no,$product_id){
    $data = Packingslip::where('slip_no',$slip_no)->where('product_id',$product_id)->first();
    $scanned_ps_stock_box = DB::table('stock_boxes')->where('slip_no',$slip_no)->where('product_id',$product_id)->where('is_scanned', 1)->count();
    $data->scanned_ps_stock_box = $scanned_ps_stock_box;
    return $data;
}

function updateSalesOrderStatusPS($order_id){
    $order_status = 2;
    $order_products = DB::table('order_products')->where('order_id', $order_id)->get();
    $isAllCompleted = 0;
    $isCompleteArr = array();
    foreach($order_products as $pro){
        if($pro->qty == $pro->release_qty){
            $isAllCompleted = 1;            
        } else {
            $isAllCompleted = 0;
        }
        $pro->is_all_completed = $isAllCompleted;
        $isCompleteArr[] = $isAllCompleted;
    }

    if(in_array(0,$isCompleteArr)){
        $order_status = 2;
    } else {
        $order_status = 4;
    }
    DB::table('orders')->where('id',$order_id)->update([
        'status' => $order_status
    ]);
}

function getInvoiceProducts($invoice_id,$product_id){
    $data = DB::table('invoice_products')->where('invoice_id',$invoice_id)->where('product_id',$product_id)->first();

    if(!empty($data)){
        $invoice = DB::table('invoice')->find($invoice_id);
        $packingslip_id = $invoice->packingslip_id;
        $packingslip = DB::table('packingslips')->find($packingslip_id);
        $slip_no = $packingslip->slipno;
        
        $count_stock = DB::table('stock_boxes')->where('product_id',$product_id)->where('is_scanned', 0)->where('is_stock_out', 0)->count();
        $invoice_ps_holding_stock = DB::table('stock_boxes')->where('product_id',$product_id)->where('slip_no',$slip_no)->count();

        $total_stock = ($count_stock + $invoice_ps_holding_stock);
        $data->count_stock = $total_stock;
    
        return $data;
    } else {
        return null;
    }

    
}

function getStoreLedgerAmount($store_id){
    // $data = Invoice::where('store_id',$store_id)->where('payment_status', '!=', 2)->get();

    $cred_amount = Ledger::where('store_id',$store_id)->where('user_type','store')->where('is_credit', 1)->sum('transaction_amount');
    $cred_amount = !empty($cred_amount)?$cred_amount:0;
    $deb_amount = Ledger::where('store_id',$store_id)->where('user_type','store')->where('is_debit', 1)->sum('transaction_amount');
    $deb_amount = !empty($deb_amount)?$deb_amount:0;

    $data = ($cred_amount - $deb_amount);

    $unpaid_halfpaid_invoices = Invoice::where('store_id',$store_id)->where('payment_status','!=',2)->get()->toArray();

    return array('outstanding'=>$data,'cred'=>$cred_amount,'deb'=>$deb_amount,'unpaid_halfpaid_invoices'=>$unpaid_halfpaid_invoices);

}

function getProductMinMaxSellPrice($from_date,$to_date,$product_id){
    $minPrice = OrderProduct::whereHas('orders', function($order){
        $order->where('status', '!=', 3);
    })->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date])->where('product_id', $product_id)->min('piece_price');

    $maxPrice = OrderProduct::whereHas('orders', function($order){
        $order->where('status', '!=', 3);
    })->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date])->where('product_id', $product_id)->max('piece_price');

    return ['minPrice'=>$minPrice,'maxPrice'=>$maxPrice];
}

function getStockPriceQty($product_id){
    $data = StockBox::selectRaw("stock_id,pcs,COUNT(id) AS count_box")->where('product_id',$product_id)->where('is_stock_out', 0)->groupBy('stock_id')->get()->toArray();

    $sumPiecePrice = 0;
    $piece_price_arr = array();
    foreach($data as $item){
        $stock_pro = \App\Models\StockProduct::where('stock_id', $item['stock_id'])->where('product_id', $product_id)->get();
        $totStockProPiecePrice = 0;
        if(!empty($stock_pro)){
            foreach($stock_pro as $pro){
                $piece_price = !empty($pro->piece_price)?$pro->piece_price:0;
                $totStockProPiecePrice += $piece_price;
            }
        }
        
       

        // dd($piece_price);
        $sumPiecePrice += !empty($totStockProPiecePrice) ? (($item['pcs'] * $totStockProPiecePrice)*$item['count_box']) : 0;
    }
    
    // dd($data);

    return ['sumPiecePrice' => $sumPiecePrice ];
    
}

function getStockPriceQtyOld($product_id){
    

    $data = StockBox::select('id','stock_id','barcode_no','pcs')->with('stock_product')->where('product_id',$product_id)->where('is_stock_out', 0)->get()->toArray();
    $sumPiecePrice = 0;
    $piece_price_arr = array();
    foreach($data as $item){
        $piece_price_arr[] = !empty($item['stock_product']['piece_price'])?$item['stock_product']['piece_price']:0;
        $sumPiecePrice += !empty($item['stock_product']['piece_price']) ? ($item['pcs'] * $item['stock_product']['piece_price']) : 0;
    }
    return ['sumPiecePrice' => $sumPiecePrice,'piece_price_arr'=>$piece_price_arr];


}

function getStockPriceAll(){
    $all_prod = Product::select('id')->get()->toArray();
    $sumPiecePriceAllFinal = 0;
    foreach($all_prod as $prod){
        // $dataall = StockBox::select('id','stock_id','barcode_no','pcs')->with('stock_product')->where('product_id',$prod['id'])->where('is_stock_out', 0)->get()->toArray();
        // $sumPiecePriceAll = 0;
        // foreach($dataall as $itemall){
        //     $piece_price = !empty($itemall['stock_product'])?$itemall['stock_product']['piece_price']:0;
        //     $sumPiecePriceAll += ($itemall['pcs'] * $piece_price);
        // }


        $getStockPriceQty = getStockPriceQty($prod['id']);
        $sumPiecePrice = $getStockPriceQty['sumPiecePrice'];
        $sumPiecePriceAllFinal += $sumPiecePrice;
    }
    return $sumPiecePriceAllFinal;
}
function getFirstLastDayMonth($yearmonthval="2023-02"){
    // $yearmonthval = "2023-02";
    // First day of the month.
    $firstday = date('Y-m-01', strtotime($yearmonthval));
    // Last day of the month.
    $lastday = date('Y-m-t', strtotime($yearmonthval));
    return array('firstday'=>$firstday,'lastday'=>$lastday);
}

function genAutoIncreNoYearWise($table,$year,$length=5){
    $val = 1;    
    $data = DB::table($table)->whereRaw("DATE_FORMAT(created_at, '%Y') = '".$year."'")->count();

    if(!empty($data)){
        $val = ($data + 1);
    }
    $number = str_pad($val,$length,"0",STR_PAD_LEFT);
    
    return $year.''.$number;
}

function getCityCommissionUser($user_id,$firstday,$lastday,$city_id=0){
    $collection_amount = 0;
    $target_status = "";
    $noTargetValueAdded = false;
    $monthly_collection_target_value = $targeted_collection_amount_commission = $commission_on_amount = $final_commission_amount = 0;
    $cityIds = array();

    $user_city = \App\Models\UserCity::with('city')->where('user_id',$user_id)->get()->toArray();
    foreach($user_city as $city){
        $cityIds[] = $city['city_id'];
    }
    // dd($city_id);
    if(!empty($city_id)){
        $cityIds = [$city_id];
    }
    if(!empty($cityIds)){        
        $collection_amount = \App\Models\PaymentCollection::whereBetween('cheque_date', [$firstday,$lastday])->whereHas('stores', function($store) use($cityIds){
            $store->whereIn('city_id',$cityIds);
        })->sum('collection_amount');
    }

    // $collection_cities = array();

    $collection_cities = \App\Models\PaymentCollection::select('cities.name AS city_name','stores.city_id')->leftJoin('stores', 'stores.id','payment_collections.store_id')->leftJoin('cities','cities.id','stores.city_id')->whereBetween('cheque_date', [$firstday,$lastday])->where('user_id', $user_id)->groupBy('stores.city_id')->get()->toArray();

    $user = \App\User::find($user_id);
    $monthly_collection_target_value = $user->monthly_collection_target_value;
    $targeted_collection_amount_commission = $user->targeted_collection_amount_commission;

    if(!empty($monthly_collection_target_value)){
        if(!empty($collection_amount)){
            if($collection_amount >= $monthly_collection_target_value){
                $commission_on_amount = ($collection_amount - $monthly_collection_target_value);
                $target_status = "Yes";
                $final_commission_amount = getPercentageVal($targeted_collection_amount_commission,$commission_on_amount);
            }else {
                $target_status = "No";
            }
        }
    } else {
        $noTargetValueAdded = true;
    }

    return array(
        'collection_amount' => $collection_amount,
        'target_status' => $target_status,
        'noTargetValueAdded' => $noTargetValueAdded,
        'commission_on_amount' => $commission_on_amount,
        'final_commission_amount' => $final_commission_amount,
        'targeted_collection_amount_commission' => $targeted_collection_amount_commission,
        'monthly_collection_target_value' => $monthly_collection_target_value,
        'noTargetValueAdded' => $noTargetValueAdded,
        'user_city' => $user_city,
        'collection_cities' => $collection_cities
    );
    
}

function getThresholdProduct($order_id,$product_id){
    $data = DB::table('product_threshold_request')->where('hold_order_id',$order_id)->where('product_id',$product_id)->first();

    return $data;
}

function openingStock($product_id,$from_date){
    $opening_from_in = StockLog::where('product_id',$product_id)->where('type','in')->where('entry_date','<',date($from_date))->sum('quantity');
    $opening_from_out = StockLog::where('product_id',$product_id)->where('type','out')->where('entry_date','<',date($from_date))->sum('quantity');

    // dd($opening_from_out);
    $opening_stock = ($opening_from_in - $opening_from_out);

    return $opening_stock;
}

function LeftWhatsappCounter($id) {
    $WhatsAppInvoice = WhatsAppInvoice::where('id', $id)->first();
    // if($WhatsAppInvoice->tb_required==0 && $WhatsAppInvoice->lr_required==0){
    //     $updated_at = $WhatsAppInvoice->updated_at;
    // }elseif($WhatsAppInvoice->tb_required==1 && $WhatsAppInvoice->lr_required==1 && isset($WhatsAppInvoice->tally_bill_file) && isset($WhatsAppInvoice->transport_lr_file)){
    //     $updated_at = $WhatsAppInvoice->updated_at;
    // }else{
    //     // $updated_at = Carbon::parse($WhatsAppInvoice->updated_at)->timezone("Asia/Kolkata");
    //     // // Add 90 minutes to the end datetime
    //     // $updated_at->addMinutes(10);
    //     $updated_at = 0;
    // }

    if ($WhatsAppInvoice->tally_bill_file && $WhatsAppInvoice->transport_lr_file) {
        $updated_at = $WhatsAppInvoice->updated_at;
    } elseif (
        ($WhatsAppInvoice->tb_required == 0 && $WhatsAppInvoice->lr_required == 0) || 
        ($WhatsAppInvoice->tb_required == 0 && isset($WhatsAppInvoice->transport_lr_file)) || 
        ($WhatsAppInvoice->lr_required == 0 && isset($WhatsAppInvoice->tally_bill_file))
    ) {
        $updated_at = $WhatsAppInvoice->updated_at;
    } else {
        $updated_at = 0;
    }
    // Parse the provided $updated_at datetime string into a Carbon instance
    $endDateTime = Carbon::parse($updated_at)->timezone("Asia/Kolkata");
    // Add 90 minutes to the end datetime
    $endDateTime->addMinutes(60);

    // Get the current datetime
    $startDateTime = Carbon::now()->timezone("Asia/Kolkata");

    // Check if the end datetime is in the past
    if ($endDateTime < $startDateTime) {
        return "Date Expired";
    }

    // Calculate the remaining time until the end datetime
    $endRemainingTime = $endDateTime->diff($startDateTime);

    // Format the remaining time into days, hours, minutes, and seconds
    $days = $endRemainingTime->d;
    $hours = $endRemainingTime->h;
    $minutes = $endRemainingTime->i;
    $seconds = $endRemainingTime->s;
    // Return the formatted remaining time
    return "Whatsapp Message: $hours h $minutes m $seconds s Left";
}

function isValidMobileNumber($mobileNumber) {
    // Remove any non-numeric characters from the mobile number
    $mobileNumber = preg_replace("/[^0-9]/", "", $mobileNumber);
    
    // Check if the mobile number has exactly 10 digits
    if(strlen($mobileNumber) === 10) {
        return true;
    } else {
        return false;
    }
}