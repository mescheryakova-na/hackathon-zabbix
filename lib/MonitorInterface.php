<?php
namespace Project;

interface MonitorInterface {

    public function __construct(ApiInterface $api, StorageInterface $storage);
    /**
     * Returns the srever list that stores in db
     * @return array
     */
    public function getServerList();

    public function updateServerListInDb();

    public function updateServerStatusListInDb();
}