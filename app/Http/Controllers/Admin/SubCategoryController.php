<?php

namespace App\Http\Controllers\Admin;

use App\Interfaces\SubcategoryInterface;
use App\Models\SubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SubCategoryController extends Controller
{
    // private SubcategoryInterface $SubcategoryRepository;

    public function __construct(SubcategoryInterface $SubcategoryRepository,Request $request)
    {
        $this->subcategoryRepository = $SubcategoryRepository;
        $this->middleware('auth:web');
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            $this->type = Auth::user()->type;
            $this->designation = Auth::user()->designation;
            // dd($this->type);
            if($this->type == 2){
                $userAccesses = userAccesses($this->designation,3);
                if(!$userAccesses){
                    abort(401);
                }
            }

            return $next($request);
        });
    }
    /**
     * This method is for show sub category list
     *
     */
    public function index(Request $request)
    {
        $term = !empty($request->term)?$request->term:'';
        // $data = $this->subcategoryRepository->getAllSubcategories();
        $data = SubCategory::select('sub_categories.*','categories.name AS cat_name')->leftJoin('categories','categories.id','sub_categories.cat_id')->where('sub_categories.name','LIKE','%'.$term.'%')->orWhere('categories.name','LIKE','%'.$term.'%')->orderBy('sub_categories.id','desc')->paginate(20);
        $data = $data->appends(['term'=>$term,'page'=>$request->page]);
        $categories = $this->subcategoryRepository->getAllCategories();
        $total = SubCategory::count();
        return view('admin.subcategory.index', compact('data', 'categories','term','total'));
    }
     /**
     * This method is for create subcategory
     *
     */
    public function store(Request $request)
    {
        $request->validate([
            "cat_id" => "required|integer",
            "name" => "required|string|max:200",
            "description" => "nullable|string"
        ]);

        // generate slug
        $slug = Str::slug($request->name, '-');
        $slugExistCount = SubCategory::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);

        // send slug
        request()->merge(['slug' => $slug]);
        $params = $request->except('_token');

        $storeData = $this->subcategoryRepository->createSubcategory($params);

        if ($storeData) {
            Session::flash('message', 'Sub category created successfully');
            return redirect()->route('admin.subcategory.index');
        } else {
            return redirect()->route('admin.subcategory.create')->withInput($request->all());
        }
    }
    /**
     * This method is for show subcategory details
     * @param  $id
     *
     */
    public function show(Request $request, $id)
    {
        $data = $this->subcategoryRepository->getSubcategoryById($id);
        $categories = $this->subcategoryRepository->getAllCategories();
        return view('admin.subcategory.detail', compact('data', 'categories'));
    }
    /**
     * This method is for subcategory update
     *
     *
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());

        $request->validate([
            "cat_id" => "nullable|integer",
            "name" => "required|string|max:200",
            "description" => "nullable|string"
        ]);

        // generate slug
        $slug = Str::slug($request->name, '-');
        $slugExistCount = SubCategory::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);

        // send slug
        request()->merge(['slug' => $slug]);

        $params = $request->except('_token');

        $storeData = $this->subcategoryRepository->updateSubcategory($id, $params);

        if ($storeData) {
            Session::flash('message', 'Sub category updated successfully');
            return redirect()->route('admin.subcategory.index');
        } else {
            return redirect()->route('admin.subcategory.create')->withInput($request->all());
        }
    }
    /**
     * This method is for update subcategory status
     * @param  $id
     *
     */
    public function status(Request $request, $id)
    {
        $storeData = $this->subcategoryRepository->toggleStatus($id);

        if ($storeData) {
            return redirect()->route('admin.subcategory.index');
        } else {
            return redirect()->route('admin.subcategory.create')->withInput($request->all());
        }
    }
    

    public function bulkSuspend(Request $request)
    {      
        if(!empty($request->suspend_check)){
            $count_data = count($request->suspend_check);
             
            $data = $this->subcategoryRepository->bulkSuspend($request->suspend_check);  

            if($data){
                Session::flash('message', $count_data.' sub categories suspended successfully');
                return redirect()->route('admin.subcategory.index');
            }else{
                return redirect()->route('admin.subcategory.index');
            }
        }else{
            return redirect()->route('admin.subcategory.index');
        }
        
          
    }
}
