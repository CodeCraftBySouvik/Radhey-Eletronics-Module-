<?php

namespace App\Interfaces;

interface SubcategoryInterface
{
    /**
     * This method is to fetch list of all subcategories
     */
    public function getAllSubcategories();
    /**
     * This method is to fetch list of all categories
     */
    public function getAllCategories();

    /**
     * This method is to get subcategory details by id
     * @param str $subcategoryId
     */
    public function getSubcategoryById($subcategoryId);

    /**
     * This method is to create subcategory
     * @param arr $categoryDetails
     */
    public function createSubcategory(array $categoryDetails);

    /**
     * This method is to update subcategory details
     * @param str $subcategoryId
     * @param arr $newDetails
     */
    public function updateSubcategory($subcategoryId, array $newDetails);

    /**
     * This method is to toggle subcategory status
     * @param str $subcategoryId
     */
    public function toggleStatus($subcategoryId);

    /**
     * This method is to delete subcategory
     * @param str $subcategoryId
     */
    public function deleteSubcategory($subcategoryId);

    public function bulkSuspend(array $array);
}
