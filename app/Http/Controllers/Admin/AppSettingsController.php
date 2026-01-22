<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AppSettingsController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('auth:web');
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            $this->type = Auth::user()->type;
            $this->designation = Auth::user()->designation;
            // dd($this->type);
            if($this->type == 2){                
                abort(401);                
            }

            return $next($request);
        });
    }
    //
    public function view()
    {
        $data = DB::table('settings')->find(1);
        return view('admin.appsettings.add', compact('data'));
    }

    public function save(Request $request)
    {
        // die('Hi');
        

        $request->validate([
            'website_name' => ['required','max:100'],
            'website_link' => ['required','max:200'],
            'po_order_address' => ['required','max:200'],
            'po_order_city' => ['required','max:150'],
            'po_order_state' => ['required','max:100'],
            'po_order_country' => ['required','max:100'],
            'po_order_pin' => ['required','max:20'],            
            // 'staff_payment_incentive' => ['required'],
            // 'order_collector_commission' => ['required'],
            // 'payment_collector_commission' => ['required'],
            'product_sales_price_threshold_percentage' => ['required'],
            'investment_percentage' => ['required'],
            'withdraw_percentage' => ['required']
        ]);

        // echo '<pre>'; print_r($request->all()); die;

        DB::table('settings')->where('id',1)->update([
            'website_name' => $request->website_name,
            'website_link' => $request->website_link,
            'po_order_address' => $request->po_order_address,
            'po_order_city' => $request->po_order_city,
            'po_order_state' => $request->po_order_state,
            'po_order_country' => $request->po_order_country,
            'po_order_pin' => $request->po_order_pin,
            // 'staff_payment_incentive' => $request->staff_payment_incentive,
            // 'order_collector_commission' => $request->order_collector_commission,
            // 'payment_collector_commission' => $request->payment_collector_commission,
            'product_sales_price_threshold_percentage' => $request->product_sales_price_threshold_percentage,
            'investment_percentage' => $request->investment_percentage,
            'withdraw_percentage' => $request->withdraw_percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        Session::flash('message', 'App settings saved successfully'); 
        return redirect()->route('admin.appsettings.view');

    }
}
