<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Journal;
use App\Models\Ledger;
use App\Models\ServiceSlip;

class ServiceSlipController extends Controller
{    
    public function __construct(Request $request)
    {
        $this->middleware('auth:web');
        $this->middleware(function ($request, $next) {
            $this->type = Auth::user()->type;
            $accessSales = userAccesses(Auth::user()->designation,9);
            if(empty(Auth::user()->designation)){
                $accessSales = true;
            }
            if(!$accessSales){               
                abort(401);                
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        # code...
        $term = !empty($request->term)?$request->term:'';
        $paginate = 20;
        $data = ServiceSlip::select('*');
        $total = ServiceSlip::select('*');
        if(!empty($term)){
            $data = $data->where('item_name', 'LIKE', '%'.$term.'%')->orWhereHas('store', function($q) use($term){
                $q->where('store_name', 'LIKE', '%'.$term.'%')->orWhere('bussiness_name', 'LIKE', '%'.$term.'%');
            });
            $total = $total->where('item_name', 'LIKE', '%'.$term.'%')->orWhereHas('store', function($q) use($term){
                $q->where('store_name', 'LIKE', '%'.$term.'%')->orWhere('bussiness_name', 'LIKE', '%'.$term.'%');
            });
        }
        $data = $data->paginate($paginate);
        $total = $total->count();
        
        return view('admin.serviceslip.index', compact('data','term','total'));
    }

    
    public function add(Request $request)
    {
        # code...
        return view('admin.serviceslip.add');
    }

    public function save(Request $request)
    {
        # code...

        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'item_name' => 'required',
            'entry_date' => 'required|date|before_or_equal:entry_date',
            'amount' => 'required|numeric'
        ]);

        $params = $request->except('_token');
        // dd($params);
        $id = ServiceSlip::insertGetId($params);   
        ## Insert Bill ##
        DB::table('bills')->insert([            
            'store_id' => $params['store_id'],
            'service_slip_id' => $id,
            'transaction_id' => $params['voucher_no'],
            'entry_date' => $params['entry_date'],
            'amount' =>  $params['amount'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')          
        ]); 
        /* Add to payment */
       
        $ledgerData = array(
            'user_type' => 'store',
            'store_id' => $params['store_id'],
            'transaction_id' => $params['voucher_no'],
            'bank_cash' => 'cash',
            'transaction_amount' => $params['amount'],
            'is_debit' => 1,
            'entry_date' => $params['entry_date'],
            'purpose' => 'service_slip',
            'purpose_description' => $params['item_name'],
            'is_gst' => 0,
            'created_at'=>date('Y-m-d H:i:s')
        );
        Ledger::insert($ledgerData);
        
        Session::flash('message', 'Service slip saved successfully'); 
        return redirect()->route('admin.service-slip.index',$params);

    }

    public function pdf($slip_id,Request $request)
    {
        # pdf for download...     
        $service_slip = ServiceSlip::find($slip_id);
        // dd($service_slip);
        // return view('admin.serviceslip.pdf', compact('service_slip'));
        $voucher_no = $service_slip->voucher_no;

        $pdf = Pdf::loadView('admin.serviceslip.pdf', compact('service_slip'));
        return $pdf->download($voucher_no.'.pdf');
    }
}
