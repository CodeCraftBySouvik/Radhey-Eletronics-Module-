<?php

namespace App\Interfaces;

interface CollectionInterface
{
    /**
     * This method is to fetch list of all collections
     */
    public function getAllCollections();

    /**
     * This method is to fetch list of all search collections
     * @param str $term
     */
    public function getSearchCollections(string $term);
    /**
     * This method is to fetch list of all sizes
     */
    public function getAllSizes();

    /**
     * This method is to fetch list of all colors
     */
    public function getAllColors();

    /**
     * This method is to get collection details by id
     * @param str $collectionId
     */
    public function getCollectionById($collectionId);

    /**
     * This method is to get collection details by slug
     * @param str $slug
     */
    public function getCollectionBySlug($slug, array $request = null);

    /**
     * This method is to create collection
     * @param arr $collectionDetails
     */
    public function createCollection(array $collectionDetails);

    /**
     * This method is to update collection details
     * @param str $collectionId
     * @param arr $newDetails
     */
    public function updateCollection($collectionId, array $newDetails);

    /**
     * This method is to toggle collection status
     * @param str $collectionId
     */
    public function toggleStatus($collectionId);

    /**
     * This method is to delete collection
     * @param str $collectionId
     */
    public function deleteCollection($collectionId);

    /**
     * This method is to delete collection
     * @param int $collectionId
     * @param array $filter
     */
    public function productsByCollection(int $collectionId, array $filter = null);
}