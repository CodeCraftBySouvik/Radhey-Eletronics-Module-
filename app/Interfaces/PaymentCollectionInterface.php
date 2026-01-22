<?php

namespace App\Interfaces;

interface PaymentCollectionInterface
{
    /**
     * This method is for show payment collection list
     *
     */
    public function listAll();
    /**
     * This method is for show payment collection details
     * @param  $id
     *
     */
    public function listByUser($id);
    public function listByStore(array $data);

     /**
     * This method is for payment collection create
     * @param array $data
     * return in array format
     */
    public function create(array $data);
     /**
     * This method is for payment collection update
     * @param array $newDetails
     * return in array format
     */
    public function update($id, array $data);
    /**
     * This method is for  update payment collection status
     * @param  $id
     *
     */
    public function toggle($id);
    /**
     * This method is for payment collection delete
     * @param  $id
     *
     */
    public function delete($id);
}
