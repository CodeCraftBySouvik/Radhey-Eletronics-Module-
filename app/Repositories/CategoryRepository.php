<?php

namespace App\Repositories;

use App\Interfaces\CategoryInterface;
use App\Models\Category;
use App\Models\Size;
use App\Models\Color;
use App\Traits\UploadAble;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CategoryRepository implements CategoryInterface
{
    use UploadAble;
    /**
     * This method is to fetch list of all categories
     */
    public function getAllCategories()
    {
        //return Category::latest('id', 'desc')->get();
        return Category::orderBy('position', 'asc')->get();
    }

    public function getAllSizes()
    {
        return Size::latest('id', 'desc')->get();
    }

    public function getAllColors()
    {
        return Color::latest('id', 'desc')->get();
    }


    /**
     * This method is to get category details by id
     * @param str $categoryId
     */
    public function getCategoryById($categoryId)
    {
        return Category::findOrFail($categoryId);
    }
    /**
     * This method is to get category details by slug
     * @param str $slug
     */
    public function getCategoryBySlug($slug)
    {
        return Category::where('slug', $slug)->with('ProductDetails')->first();
    }
    /**
     * This method is to delete category
     * @param str $categoryId
     */
    public function deleteCategory($categoryId)
    {
        Category::destroy($categoryId);
    }
    /**
    * This method is to create category
    **/
    public function createCategory(array $categoryDetails)
    {
        
        $collection = collect($categoryDetails);

        $category = new Category;
        $category->name = $collection['name'];
        $category->description = $collection['description'];
        $category->created_at  = date('Y-m-d H:i:s');
        // generate slug
        $slug = Str::slug($collection['name'], '-');
        $slugExistCount = Category::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);
        $category->slug = $slug;

        $category->save();

        return $category;
    }
    /**
    * This method is to update category details     
    **/
    public function updateCategory($categoryId, array $newDetails)
    {
        
        $category = Category::findOrFail($categoryId);
        $collection = collect($newDetails);

        $category->name = $collection['name'];
        $category->description = $collection['description'];

        // generate slug
        $slug = Str::slug($collection['name'], '-');
        $slugExistCount = Category::where('slug', $slug)->count();
        if ($slugExistCount > 0) $slug = $slug.'-'.($slugExistCount+1);
        $category->slug = $slug;

        $category->save();
        return $category;
    }
    /**
     * This method is to toggle category status
     * @param str $categoryId
     */
    public function toggleStatus($id){
        $category = Category::findOrFail($id);

        $status = ( $category->status == 1 ) ? 0 : 1;
        $category->status = $status;
        $category->save();

        return $category;
    }

    public function bulkSuspend(array $array)
    {
        Category::whereIn('id', $array)->update(['status' => 0]);
        return true;
    }


}
