<?php

namespace App\Interfaces;

interface CategoryInterface
{
    /**
     * This method is to fetch list of all categories
     */
    public function getAllCategories();
    public function getAllSizes();
    public function getAllColors();

    /**
     * This method is to get category details by id
     * @param str $categoryId
     */
    public function getCategoryById($categoryId);

    /**
     * This method is to create category
     * @param arr $categoryDetails
     */
    public function createCategory(array $categoryDetails);

    /**
     * This method is to update category details
     * @param str $categoryId
     * @param arr $newDetails
     */
    public function updateCategory($categoryId, array $newDetails);

    /**
     * This method is to toggle category status
     * @param str $categoryId
     */
    public function toggleStatus($categoryId);

    /**
     * This method is to delete category
     * @param str $categoryId
     */
    public function deleteCategory($categoryId);

   /**
     * This method is to get category details by slug
     * @param str $slug
     */
    public function getCategoryBySlug($slug);


    public function bulkSuspend(array $array);

}

