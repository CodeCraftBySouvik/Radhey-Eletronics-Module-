<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Interfaces\CustomerInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    //
    public function __construct(CustomerInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request)
    {
        $term = !empty($request->term)?$request->term:'';
        // $data = $this->customerRepository->listAll();
        $data = Customer::where('name', 'LIKE', '%'.$term.'%')->paginate(5);
        $data = $data->appends(['term'=>$term,'page'=>$request->page]);
        $total = Customer::count();
        return view('admin.customer.index', compact('data','term','total'));
    }

    public function create(Request $request)
    {
        return view('admin.customer.create');
    }

    public function store(Request $request)
    {
        # code...
        $request->validate([
            "name" => "required|string|max:255",
            "whatsapp_no" => "required|integer",
            "gst_number" => "required",
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

        $customerData = $this->customerRepository->create($params);

        

        if ($customerData) {
            Session::flash('message', 'Customer updated successfully');
            return redirect()->route('admin.customer.index');
        } else {
            return redirect()->route('admin.customer.create')->withInput($request->all());
        }
    }

    public function show(Request $request, $id)
    {
        $data = $this->customerRepository->listById($id);
        // dd($data);
        return view('admin.customer.detail', compact('data'));
    }

    public function edit($id)
    {
        $data = $this->customerRepository->listById($id);
        return view('admin.customer.edit', compact('data'));
    }

    public function update($id,Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "whatsapp_no" => "required|integer",
            "gst_number" => "required",
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

        $customerData = $this->customerRepository->update($id,$params);

        

        if ($customerData) {
            Session::flash('message', 'Customer updated successfully');
            return redirect()->route('admin.customer.index');
        } else {
            return redirect()->route('admin.customer.create')->withInput($request->all());
        }
    }

    public function status($id)
    {
        $data = $this->customerRepository->toggle($id);
        return redirect()->route('admin.customer.index');
    }

    public function bulkSuspend(Request $request)
    {
        // dd($request->all());
        if(!empty($request->suspend_check)){
            $data = $this->customerRepository->bulkSuspend($request->suspend_check);  

            if($data){
                Session::flash('message', 'Suspended successfully');
                return redirect()->route('admin.customer.index');
            }else{
                return redirect()->route('admin.customer.index');
            }
        }else{
            return redirect()->route('admin.customer.index');
        }
        
    }
}
