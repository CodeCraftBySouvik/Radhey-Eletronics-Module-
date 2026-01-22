<?php

namespace App\Interfaces;

interface OrderInterface
{
    public function listAll();
    public function listById($id);
    public function listByStatus($status);
    public function searchOrder(string $term);
    public function create(array $data);
    public function update($id, array $data);
    public function toggle($id, $status);
    public function listByorderId($id);
    public function listByUserId($id);
    public function storeFilter($id);
    public function placeOrder(array $data);
    public function placeOrderUpdated(array $data);

    public function Totalsales($id);
    public function latestsales($storeId);
    public function lastOrder($storeId);
    public function avgOrder($storeId);
    public function lastVisit($storeId);
    public function Totalamount($storeId,$productId);
    public function TotalOrder($storeId,$productId);
    // public function delete($id);
}
