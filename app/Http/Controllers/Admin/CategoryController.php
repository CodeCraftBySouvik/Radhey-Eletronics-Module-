<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\CategoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    //private CategoryInterface $categoryRepository;

    public function __construct(CategoryInterface $categoryRepository,Request $request)
    {
        $this->categoryRepository = $categoryRepository;
        $this->middleware('auth:web');
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            $this->type = Auth::user()->type;
            $this->designation = Auth::user()->designation;
            // dd($this->type);
            if($this->type == 2){
                $userAccesses = userAccesses($this->designation,2);
                if(!$userAccesses){
                    abort(401);
                }
            }

            return $next($request);
        });
    }
    /**
     * This method is for show category list
     *
     */
    public function index(Request $request)
    {
        $term = !empty($request->term)?$request->term:'';
        // $data = $this->categoryRepository->getAllCategories();
        $data = Category::where('name','LIKE','%'.$term.'%')->paginate(20);
        $total = Category::count();
        $data = $data->appends(['term'=>$term,'page'=>$request->page]);
        return view('admin.category.index', compact('data','term','total'));
    }
    /**
     * This method is for create category
     *
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            "name" => "required|string|max:100",
            "description" => "nullable|string"
        ]);

        // generate slug
        $slug = Str::slug($request->name, '-');
        $slugExistCount = Category::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);

        // send slug
        request()->merge(['slug' => $slug]);
        $params = $request->except('_token');

        $categoryStore = $this->categoryRepository->createCategory($params);

        if ($categoryStore) {
            Session::flash('message', 'Category created successfully');
            return redirect()->route('admin.category.index');
        } else {
            return redirect()->route('admin.category.create')->withInput($request->all());
        }
    }
    /**
     * This method is for show category details
     * @param  $id
     *
     */
    public function show(Request $request, $id)
    {
        $data = $this->categoryRepository->getCategoryById($id);
        return view('admin.category.detail', compact('data'));
    }
    /**
     * This method is for category update
     * @param  $id
     *
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());

        $request->validate([
            "name" => "required|string|max:100",
            "description" => "nullable|string"
        ]);

        // generate slug
        $slug = Str::slug($request->name, '-');
        $slugExistCount = Category::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);

        // send slug
        request()->merge(['slug' => $slug]);

        $params = $request->except('_token');

        $categoryStore = $this->categoryRepository->updateCategory($id, $params);

        if ($categoryStore) {
            Session::flash('message', 'Category updated successfully');
            return redirect()->route('admin.category.index');
        } else {
            return redirect()->route('admin.category.create')->withInput($request->all());
        }
    }
    /**
     * This method is for update category status
     * @param  $id
     *
     */
    public function status(Request $request, $id)
    {
        $categoryStat = $this->categoryRepository->toggleStatus($id);

        if ($categoryStat) {
            return redirect()->route('admin.category.index');
        } else {
            return redirect()->route('admin.category.create')->withInput($request->all());
        }
    }
    
    public function bulkSuspend(Request $request)
    {      
        if(!empty($request->suspend_check)){
            $count_data = count($request->suspend_check);
             
            $data = $this->categoryRepository->bulkSuspend($request->suspend_check);  

            if($data){
                Session::flash('message', $count_data.' categories suspended successfully');
                return redirect()->route('admin.category.index');
            }else{
                return redirect()->route('admin.category.index');
            }
        }else{
            return redirect()->route('admin.category.index');
        }
        
          
    }
}
