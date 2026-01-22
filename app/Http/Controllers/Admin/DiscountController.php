<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Discount;
use App\Models\Ledger;
use App\Models\Store;
use App\Models\Payment;
use App\Models\Journal;


class DiscountController extends Controller
{
    //

    public function list(Request $request)
    {
        # list discounts...
        $paginate = 20;
        $search = !empty($request->search)?$request->search:'';
        $data = Discount::select('*');
        $total = Discount::select('*');
        
        if(!empty($search)){
            $data = $data->where('voucher_no', 'LIKE', '%'.$search.'%')->orWhereHas('store', function($s) use ($search){
                $s->where('store_name', 'LIKE', '%'.$search.'%')->orWhere('bussiness_name','LIKE', '%'.$search.'%');
            });
            $total = $total->where('voucher_no', 'LIKE', '%'.$search.'%')->orWhereHas('store', function($s) use ($search){
                $s->where('store_name', 'LIKE', '%'.$search.'%')->orWhere('bussiness_name','LIKE', '%'.$search.'%');
            });
        }

        $data = $data->orderBy('id','desc')->paginate($paginate);
        $total = $total->count();
        
        return view('admin.discount.list', compact('data','total','search'));
    }

    public function add(Request $request)
    {
        # add discount for store...
        return view('admin.discount.add');
    }

    public function save(Request $request)
    {
        # save discount...
        $request->validate([
            'amount' => 'required|digits_between:1,9999999999',
            'store_id' => 'required|exists:stores,id',
            'payment_mode' => 'required',
            'chq_utr_no' => 'required_unless:payment_mode,cash',
            'bank_name' => 'required_unless:payment_mode,cash',
        ],[
            'amount.required' => 'Please add discount amount',
            'amount.digits_between' => 'Please add valid amount',
            'store_id.required' => 'Please add customer',
            'payment_mode.required' => 'Please add mode of payment',
            'chq_utr_no.required_unless' => 'Please add Cheque No or UTR No',
            'bank_name.required_unless' => 'Please add bank name',
        ]);
        $params = $request->except('_token');
        // dd($params);
        # Discount table entry
        $discountArr = array(
            'voucher_no' => $params['voucher_no'],
            'store_id' => $params['store_id'],
            'entry_date' => $params['entry_date'],
            'amount' => $params['amount'],
            'narration' => $params['narration'],
            'created_by' => Auth::user()->id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $discount_id = Discount::insertGetId($discountArr);
        # Payment Entry
        $paymentArr = array(
            'store_id' => $params['store_id'],
            'discount_id' => $discount_id,
            'voucher_no' => $params['voucher_no'],
            'payment_date' => $params['entry_date'],
            'payment_mode' => $params['payment_mode'],
            'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,
            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
            'is_gst' => ($params['payment_mode'] == 'cash') ? 0 : 1, 
            'amount' => $params['amount'],
            'bank_name' => $params['bank_name'],
            'chq_utr_no' => $params['chq_utr_no'],
            'narration' => $params['narration'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $payment_id = Payment::insertGetId($paymentArr);
        # Ledger Entry as Store Credit
        $ledgerArr = array(
            'user_type' => 'store',
            'store_id' => $params['store_id'],
            'payment_id' => $payment_id,
            'transaction_id' => $params['voucher_no'],
            'transaction_amount' => $params['amount'],
            'is_credit' => 1,
            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
            'is_gst' => ($params['payment_mode'] == 'cash') ? 0 : 1, 
            'entry_date' => $params['entry_date'],
            'purpose' => 'discount',
            'purpose_description' => 'Store Discounts',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        Ledger::insert($ledgerArr);
        # Journal Entry as debit
        $journalArr = array(
            'payment_id' => $payment_id,
            'transaction_amount' => $params['amount'],
            'is_debit' => 1,
            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
            'is_gst' => ($params['payment_mode'] == 'cash') ? 0 : 1, 
            'purpose' => 'store_discount',
            'purpose_description' => 'Store Discounts',
            'purpose_id' => $params['voucher_no'],
            'entry_date' => $params['entry_date'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        Journal::insert($journalArr);

        Session::flash('message', 'Discount added successfully'); 
        return redirect()->route('admin.discount.list');

    }

    public function edit($id)
    {
        # edit form...
        $data = Discount::find($id);
        return view('admin.discount.edit', compact('data','id'));
    }

    public function update($id,Request $request)
    {
        # update...
        $request->validate([
            'amount' => 'required'
        ]);

        $params = $request->except('_token');
        // dd($params);
        $payment_id = $params['payment_id'];
        $discountArr = array(
            'entry_date' => $params['entry_date'],
            'amount' => $params['amount'],
            'narration' => $params['narration'],
            'updated_by' => Auth::user()->id,
            'updated_at' => date('Y-m-d H:i:s')
        );

        Discount::where('id',$id)->update($discountArr);

        $paymentArr = array(            
            'payment_date' => $params['entry_date'],
            'payment_mode' => $params['payment_mode'],
            'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,
            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
            'is_gst' => ($params['payment_mode'] == 'cash') ? 0 : 1, 
            'amount' => $params['amount'],
            'bank_name' => $params['bank_name'],
            'chq_utr_no' => $params['chq_utr_no'],
            'narration' => $params['narration'],
            'updated_at' => date('Y-m-d H:i:s')
        );
        // dd($paymentArr);
        Payment::where('id',$payment_id)->update($paymentArr);
        # Ledger Entry as Store Credit
        $ledgerArr = array(
            'transaction_amount' => $params['amount'],
            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
            'is_gst' => ($params['payment_mode'] == 'cash') ? 0 : 1, 
            'entry_date' => $params['entry_date'],
            'purpose' => 'discount',
            'purpose_description' => 'Store Discounts',
            'updated_at' => date('Y-m-d H:i:s')
        );
        // dd($ledgerArr);
        Ledger::where('payment_id', $payment_id)->update($ledgerArr);
        # Journal Entry as debit
        $journalArr = array(
            'transaction_amount' => $params['amount'],
            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
            'is_gst' => ($params['payment_mode'] == 'cash') ? 0 : 1,             
            'entry_date' => $params['entry_date'],
            'updated_at' => date('Y-m-d H:i:s')
        );
        // dd($journalArr);
        Journal::where('payment_id', $payment_id)->update($journalArr);

        
        Session::flash('message', 'Discount updated successfully'); 
        return redirect()->route('admin.discount.list');

    }
}
