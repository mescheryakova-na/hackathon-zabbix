<?php
namespace Project;

interface StorageInterface {
    /**
     * Returns the srever list that stores in the storage
     * @return array
     */
    public function getServerList();

    /**
     * @param array $data
     * @return bool
     */
    public function updateServerInfo(array $data);

    /**
     * @param array $data
     * @return bool
     */
    public function addServerInfo(array $data);
}