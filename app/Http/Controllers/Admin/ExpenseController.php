<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
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
                $userAccesses = userAccesses($this->designation,5);
                if(!$userAccesses){
                    abort(401);
                }
            }

            return $next($request);
        });
    }

    public function index($parent_id,Request $request)
    {        
        $term = !empty($request->term)?$request->term:'';
        $cred_deb = !empty($request->cred_deb)?$request->cred_deb:'';
        $user_type = !empty($request->user_type)?$request->user_type:'';

        $data = DB::table('expense AS exp')->select('exp.*')->where('parent_id',$parent_id);
        $total = DB::table('expense AS exp')->where('exp.parent_id',$parent_id); 
        
        if(!empty($term)){
            $data = $data->where('exp.title', 'LIKE', '%'.$term.'%' );
            $total = $total->where('exp.title', 'LIKE', '%'.$term.'%' );
        }
        if(!empty($cred_deb)){
            if($cred_deb == 'credit'){
                $data = $data->where('exp.for_credit', 1);
                $total = $total->where('exp.for_credit', 1);
            } else {
                $data = $data->where('exp.for_credit', 1);
                $total = $total->where('exp.for_credit', 1);
            }
            
        }
        if(!empty($user_type)){
            if($user_type == 'store'){
                $data = $data->where('exp.for_store', 1);
                $total = $total->where('exp.for_store', 1);
            } else if ($user_type == 'staff'){
                $data = $data->where('exp.for_staff', 1);
                $total = $total->where('exp.for_staff', 1);
            } else if ($user_type == 'partner'){
                $data = $data->where('exp.for_partner', 1);
                $total = $total->where('exp.for_partner', 1);
            }
            $data = $data->where('exp.title', 'LIKE', '%'.$term.'%' );
        }
        
        
        $data = $data->orderBy('exp.id','desc')->paginate(20);
        $total = $total->count();

        $data = $data->appends([
            'term'=>$term,
            'page'=>$request->page,
            'user_type' => $user_type,
            'cred_deb' => $cred_deb
        ]);
          
        return view('admin.expense.index', compact('data', 'term','parent_id','total','user_type','cred_deb'));
    }

    
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            "title" => "required|string|max:100|unique:expense,title",
            "description" => "max:500"
        ]);

        $params = $request->except('_token');   
        
        
        
        $newEntry = new Expense;
        $newEntry->parent_id = $params['parent_id'];
        $newEntry->title = !empty($params['title'])?$params['title']:'';

        $slug = Str::slug($newEntry->title, '-');
        $slugExistCount = DB::table('expense')->where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);

        $newEntry->slug = $slug;
        $newEntry->description = !empty($params['description'])?$params['description']:'';
        $newEntry->for_debit = !empty($params['for_debit'])?$params['for_debit']:0;
        $newEntry->for_credit = !empty($params['for_credit'])?$params['for_credit']:0;
        $newEntry->for_staff = !empty($params['for_staff'])?$params['for_staff']:0;
        $newEntry->for_store = !empty($params['for_store'])?$params['for_store']:0;
        $newEntry->for_partner = !empty($params['for_partner'])?$params['for_partner']:0;
        $newEntry->created_at = date('Y-m-d H:i:s');
        $newEntry->save();
                    
        if ($newEntry) {
            Session::flash('message', 'Expense created successfully');
            return redirect()->route('admin.expense.index',$params['parent_id']);
        } else {
            return redirect()->route('admin.expense.index',$params['parent_id'])->withInput($request->all());
        }        
    }

    public function show($id)
    {
        $data = DB::table('expense AS c1')->select('c1.*','c2.title AS parent_title')->leftJoin('expense AS c2','c2.id','c1.parent_id')->where('c1.id',$id)->first();
        
        return view('admin.expense.detail', compact('data'));
    }

    public function status(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $parent_id = $expense->parent_id;
        $status = ( $expense->status == 1 ) ? 0 : 1;
        $expense->status = $status;
        $expense->save();

        if ($expense) {
            Session::flash('message', 'Status changed successfully');   
            return redirect()->route('admin.expense.index',$parent_id);
        } else {
            return redirect()->route('admin.expense.create')->withInput($request->all());
        }
    }

    public function update($id,Request $request)
    {
        $request->validate([
            "title" => "required|string|max:100|unique:expense,title,".$id,
            "description" => "max:500"
        ]);
        
        $params = $request->except('_token');   
        // dd($params);     
        
        $updatedEntry = Expense::findOrFail($id);
        $updatedEntry->parent_id = $params['parent_id'];
        $updatedEntry->title = $params['title'];
        $updatedEntry->description = $params['description'];
        $updatedEntry->for_debit = !empty($params['for_debit'])?$params['for_debit']:0;
        $updatedEntry->for_credit = !empty($params['for_credit'])?$params['for_credit']:0;
        $updatedEntry->for_staff = !empty($params['for_staff'])?$params['for_staff']:0;
        $updatedEntry->for_store = !empty($params['for_store'])?$params['for_store']:0;
        $updatedEntry->for_partner = !empty($params['for_partner'])?$params['for_partner']:0;
        $updatedEntry->save();

        if ($updatedEntry) {
            Session::flash('message', 'Expense updated successfully');
            return redirect()->route('admin.expense.index', $params['parent_id']);
        } else {
            // dd($request->all());
            return redirect()->route('admin.expense.view', $request->id)->withInput($request->all());
        }
                
    }

    public function bulkSuspend(Request $request)
    {
        $parent_id = $request->parent_id;
        if(!empty($request->suspend_check)){
            $total_count = count($request->suspend_check);
            $array = $request->suspend_check;
            Expense::whereIn('id', $array)->update(['status' => 0]);
            Session::flash('message', $total_count.' expenses suspended successfully');            
            return redirect()->route('admin.expense.index',$parent_id);            
        }else{
            return redirect()->route('admin.expense.index',$parent_id);
        }
        
          
    }
}
