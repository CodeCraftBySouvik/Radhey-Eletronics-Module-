<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Models\Store;

class LedgerController extends Controller
{
    
    public function getUsersByType(Request $request)
    {
        $type = $request->type;
        $search = $request->term;
        $data = array();
        if(!empty($type)){
            if($type == 'staff'){
                $data = DB::table('users')->select('id','name')->where('name','LIKE','%'.$search.'%')->where('type',2)->orderBy('name')->get();
            }else if($type == 'store'){
                $data = DB::table('stores')->select('id','store_name AS name','bussiness_name');
                $data = $data->where('store_name','LIKE','%'.$search.'%')->orWhere('bussiness_name','LIKE','%'.$search.'%');                                
                $data = $data->orderBy('bussiness_name','asc')->get();
            }else if($type == 'partner'){
                $data = DB::table('users')->select('id','name','email','mobile')->where('name','LIKE','%'.$search.'%')->where('type',1)->get();
            }else if($type == 'supplier'){
                $data = DB::table('suppliers')->select('id','name')->where('name','LIKE','%'.$search.'%')->where('status',1)->get();
            }
    
        }
        
        return $data;
    }

    public function getRequiredExpenses(Request $request)
    {
        $user_type = $request->user_type;
        $payment_for = 'debit';

        $data = DB::table('expense');
        if($user_type == 'store'){
            $data = $data->where('for_store', 1);
        }else if($user_type == 'staff'){
            $data = $data->where('for_staff', 1);
        }else if($user_type == 'partner'){
            $data = $data->where('for_partner', 1);
        }else if($user_type == 'supplier'){
            $data = $data->where('slug', 'supplier-payment');
        }

        if($payment_for == 'credit'){
            $data = $data->where('for_credit', 1);
        }else{
            $data = $data->where('for_debit', 1);
        }
        $data = $data->where('status', 1)->get();

        return $data;
    }

    public function getBankList(Request $request)
    {
        $search = !empty($request->term)?$request->term:'';
        $data = DB::table('bank_lists')->where('name', 'LIKE', '%'.$search.'%')->orderBy('name','asc')->get();

        return $data;
    }

    public function storeSearch(Request $request)
    {
        # store search ajax...
        $search = !empty($request->search)?$request->search:'';
        $idnotin = !empty($request->idnotin)?$request->idnotin:array();

        $data = Store::select('*');
        if(!empty($idnotin)){
            $data = $data->whereNotIn('id', $idnotin);
        }
        if(!empty($search)){
            $data = $data->where(function($q) use($search){
                $q->where('bussiness_name', 'LIKE', '%'.$search.'%')->orWhere('store_name', 'LIKE', '%'.$search.'%');
            });
        }
        $data = $data->orderBy('bussiness_name')->get();

        return $data;
    }

    public function searchCities(Request $request)
    {
        # Search Cities for User Assign...
        $parent_id = !empty($request->parent_id)?$request->parent_id:'';
        $idnotin = !empty($request->idnotin)?$request->idnotin:array();
        $search = !empty($request->search)?$request->search:'';

        $data = DB::table('cities')->whereNotNull('parent_id');
        if(!empty($parent_id)){
            $data = $data->where('parent_id',$parent_id);
        }
        if(!empty($idnotin)){
            $data = $data->whereNotIn('id', $idnotin);
        }
        if(!empty($search)){
            $data = $data->where('name', 'LIKE', '%'.$search.'%');
        }
        $data = $data->get();
        return $data;
    }

    public function getStoreLedgerAmount(Request $request)
    {
        $store_id = $request->store_id;
        $data = getStoreLedgerAmount($store_id);
        return $data;
    }

    
}
