<?php

namespace App\Interfaces;

interface ProductInterface
{
    public function listAll();
    public function getSearchProducts(string $term);
    public function categoryList($isActive=0);
    public function subCategoryList($isActive=0,$catId=0);    
    public function listById($id);
    public function listBySlug($slug);
    public function relatedProducts($id);
    public function listImagesById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function toggle($id);
    public function delete($id);
    public function deleteSingleImage($id);
    public function wishlistCheck($productId);
    public function getProductByCategoryId($categoryId);
    public function productlistByuse($only_for);
    public function bulkSuspend(array $array);
}
