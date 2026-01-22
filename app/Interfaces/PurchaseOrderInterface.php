<?php

namespace App\Interfaces;

interface PurchaseOrderInterface
{
    public function listAll();
    public function getSearchPurchaseOrders(string $term);
    public function create(array $data);
    public function update(array $data);
    public function delete($id);
    public function bulkSuspend(array $array);    
    public function po_product_list($unique_id);
    public function showboxes($unique_id);
    public function saveInventory(array $array);
    public function showProductBoxes($unique_id);
    public function addBox($unique_id,$product_id);
    public function deleteBox($unique_id,$product_id,$barcode_no);
    public function archiveBox($unique_id,$product_id,$barcode_no);
    public function viewStocks($unique_id);
    public function productStock($product_id);
}
