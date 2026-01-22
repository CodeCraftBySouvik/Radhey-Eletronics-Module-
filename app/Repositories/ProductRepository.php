<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Interfaces\ProductInterface;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\SubCategory;
use App\Models\Wishlist;
use App\Traits\UploadAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductInterface
{
    use UploadAble;

    public function listAll()
    {
        return Product::orderBy('id','desc')->get();
    }

    public function categoryList($isActive=0)
    {
        if(empty($isActive)){
            return Category::all();
        }else{
            return Category::where('status', 1)->get();
        }
        
    }
    public function getSearchProducts(string $term)
    {
        return Product::where('name', 'LIKE', '%' . $term . '%')
            ->orWhere('short_desc', 'LIKE', '%' . $term . '%')
            ->orWhere('desc', 'LIKE', '%' . $term . '%')
            ->orderBy('id','desc')->paginate(5);
    }

    public function subCategoryList($isActive=0,$catId=0)
    {
        if(empty($isActive)){
            $data = SubCategory::all();
        }else{
            if(empty($catId)){
                $data = SubCategory::orderBy('name', 'asc')->where('status', 1)->get();
            }else{
                // die($catId);
                $data = SubCategory::orderBy('name', 'asc')->where('status', 1)->where('cat_id',$catId)->get();
            }
            
        }

        return $data;
        
    }
    
    public function listById($id)
    {
        return Product::where('id',$id)->with('category','subcategory')->get();
    }

    public function listBySlug($slug)
    {
        return Product::where('slug', $slug)->with('category', 'subCategory')->first();
    }

    public function relatedProducts($id)
    {
        $product = Product::findOrFail($id);
        $cat_id = $product->cat_id;
        return Product::where('cat_id', $cat_id)->where('id', '!=', $id)->with('category', 'subCategory')->get();
    }
    /**
     * This method is to get product details by category id
     * @param str $categoryId
     */
    public function getProductByCategoryId($categoryId){

        return Product::where('cat_id', $categoryId)->where('status', '1')->with('category')->get();
    }


    /**
     * This method is to get product details by collection id
     * @param str $collectionId
     */
    public function getProductByCollectionId($collectionId){
        return Product::where('collection_id', $collectionId)->with('collection')->get();
    }
    /**
     * This method is to get product details by category id
     * @param str $categoryId
     */
    public function productlistByuse($only_for)
    {

        return Product::where('only_for', $only_for)->get();
    }
    public function listImagesById($id)
    {
        return ProductImage::where('product_id', $id)->latest('id')->get();
    }

    public function create(array $data)
    {
        // dd($data);
        DB::beginTransaction();

        try {
            $collectedData = collect($data);
            $newEntry = new Product;
            $newEntry->cat_id = $collectedData['cat_id'];
            $newEntry->sub_cat_id = $collectedData['sub_cat_id'];
            $newEntry->name = $collectedData['name'];
            $newEntry->hsn_code = !empty($collectedData['hsn_code'])?$collectedData['hsn_code']:'';
            $newEntry->pcs = !empty($collectedData['pcs'])?$collectedData['pcs']:0;
            $newEntry->short_desc = !empty($collectedData['short_desc'])?$collectedData['short_desc']:'';
            $newEntry->desc = !empty($collectedData['desc'])?$collectedData['desc']:'';
            $newEntry->igst = !empty($collectedData['igst'])?$collectedData['igst']:18;
            $newEntry->cgst = !empty($collectedData['cgst'])?$collectedData['cgst']:9;
            $newEntry->sgst = !empty($collectedData['sgst'])?$collectedData['sgst']:9;
            
            // slug generate
            $slug = \Str::slug($collectedData['name'], '-');
            $slugExistCount = Product::where('slug', $slug)->count();
            if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);
            $newEntry->slug = $slug;

            // main image handling
            $upload_path = "public/uploads/product/";
            // echo $upload_path; die;
            if(isset($collectedData['image'])){
                $image = $collectedData['image'];
                $imageName = time() . "." . $image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $newEntry->image = $upload_path . $uploadedImage;
            } else {
                $newEntry->image = '';
            }
            
            $newEntry->save();

            // multiple image upload handling
            // if (isset($data['product_images'])) {
            //     $multipleImageData = [];
            //     foreach ($data['product_images'] as $imagekey => $imagevalue) {
            //         $imageName = mt_rand() . '-' . time() . "." . $image->getClientOriginalName();
            //         $imagevalue->move($upload_path, $imageName);
            //         $image_path = $upload_path . $imageName;
            //         $multipleImageData[] = [
            //             'product_id' => $newEntry->id,
            //             'image' => $image_path
            //         ];
            //     }
            //     if (count($multipleImageData) > 0) ProductImage::insert($multipleImageData);
            // }

            DB::commit();
            return $newEntry;
        } catch (\Throwable $th) {
            throw $th;
            DB::rollback();
        }
    }

    public function update($id, array $newDetails)
    {
        // dd($id);

        DB::beginTransaction();
        try {
            $upload_path = "public/uploads/product/";
            $updatedEntry = Product::findOrFail($id);
            // dd($updatedEntry);
            $collectedData = collect($newDetails);     
            // dd($collectedData);       
            if (!empty($collectedData['cat_id'])) $updatedEntry->cat_id = $collectedData['cat_id'];
            if (!empty($collectedData['sub_cat_id'])) $updatedEntry->sub_cat_id = $collectedData['sub_cat_id'];
            $updatedEntry->name = $collectedData['name'];
            $updatedEntry->hsn_code = !empty($collectedData['hsn_code'])?$collectedData['hsn_code']:'';
            $updatedEntry->pcs = !empty($collectedData['pcs'])?$collectedData['pcs']:0;
            $updatedEntry->short_desc = !empty($collectedData['short_desc'])?$collectedData['short_desc']:'';
            $updatedEntry->desc = !empty($collectedData['desc'])?$collectedData['desc']:'';
            $updatedEntry->igst = !empty($collectedData['igst'])?$collectedData['igst']:18;
            $updatedEntry->cgst = !empty($collectedData['cgst'])?$collectedData['cgst']:9;
            $updatedEntry->sgst = !empty($collectedData['sgst'])?$collectedData['sgst']:9;
            
            // slug generate
            $slug = \Str::slug($collectedData['name'], '-');
            $slugExistCount = Product::where('slug', $slug)->count();
            if ($slugExistCount > 0) $slug = $slug . '-' . ($slugExistCount + 1);

            $updatedEntry->slug = $slug;

            if (isset($newDetails['image'])) {
                // delete old image
                if (Storage::exists($updatedEntry->image)) unlink($updatedEntry->image);

                $image = $collectedData['image'];
                $imageName = time() . "." . $image->getClientOriginalName();
                $image->move($upload_path, $imageName);
                $uploadedImage = $imageName;
                $updatedEntry->image = $upload_path . $uploadedImage;
                
            }
            // dd($updatedEntry);
            $updatedEntry->save();

            // multiple image upload handling
            // if (isset($newDetails['product_images'])) {
            //     $multipleImageData = [];
            //     foreach ($newDetails['product_images'] as $imagekey => $imagevalue) {
            //         $imageName = mt_rand() . '-' . time() . "." . $imagevalue->getClientOriginalName();
            //         $imagevalue->move($upload_path, $imageName);
            //         $image_path = $upload_path . $imageName;
            //         $multipleImageData[] = [
            //             'product_id' => $id,
            //             'image' => $image_path
            //         ];
            //     }

            //     // dd($multipleImageData);

            //     if (count($multipleImageData) > 0) {
            //         ProductImage::insert($multipleImageData);
            //     }
            // }
            

            DB::commit();
            return $updatedEntry;
        } catch (\Throwable $th) {
        //    dd($th);
            throw $th;
            DB::rollback();
        }
    }

    public function toggle($id)
    {
        $updatedEntry = Product::findOrFail($id);

        $status = ($updatedEntry->status == 1) ? 0 : 1;
        $updatedEntry->status = $status;
        $updatedEntry->save();

        return $updatedEntry;
    }

    public function delete($id)
    {
        Product::destroy($id);
    }

    public function deleteSingleImage($id)
    {
        ProductImage::destroy($id);
    }

    public function wishlistCheck($productId)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $data = Wishlist::where('product_id', $productId)->where('ip', $ip)->first();
        return $data;
    }

    public function bulkSuspend(array $array)
    {
        Product::whereIn('id', $array)->update(['status' => 0]);
        return true;
    }
}
