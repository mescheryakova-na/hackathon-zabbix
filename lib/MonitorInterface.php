<?php
namespace Project;

interface MonitorInterface {

    public function __construct(ApiInterface $api, DbInterface $db);
    /**
     * Returns the srever list that stores in db
     * @return array
     */
    public function getServerList();

    public function updateServerListInDb();

    public function updateServerStatusListInDb();
}