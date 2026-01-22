<?php

namespace App\Interfaces;

interface StoreInterface
{
    /**
     * This method is to get store details
     *
     */
    public function listAll();
    /**
     * This method is to get user details
     *
     */
    public function listUsers();
    /**
     * This method is to get store details by id
     * @param  $id
     */
    public function listById($id);
    /**
     * This method is to get store details by slug
     * @param  $slug
     */
    public function listBySlug($slug);
    /**
     * This method is to create store
     *@param  $data
     */
    public function create(array $data);
    /**
     * This method is to create store
     *@param array $data
     */
    public function update($id, array $data);
    /**
     * This method is to change store status
     *@param  $id
     */
    public function toggle($id);
     /**
     * This method is to delete store
     *@param  $id
     */
    public function delete($id);



    /**
     * This method is to update store through API
     *@param  $id
     *
     */
    public function storeupdate($id, array $newDetails);

    /**
     * This method is to submit no order reason
     *
     *
     */
    public function noorderreasonupdate(array $data);

    
    public function bulkSuspend(array $array);

}
