<?php
namespace Project\Test;

use Project\ApiInterface;
use Project\DbInterface;
use Project\MonitorInterface;

class TestEverythingIsOk implements MonitorInterface {

    protected $db;
    protected $api;

    public function __construct(ApiInterface $api, DbInterface $db)
    {
        $this->api = $api;
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getServerList()
    {
        $db = $this->db;
        $result = $db->selectArray('servers', []);

        foreach ($result as $k => $server) {
            $result[$k]['status'] = 'OK';
            $result[$k]['priority'] = '-1';
            $result[$k]['message'] = '';
        }
        return $result;
    }

    public function updateServerListInDb()
    {
        return true;
    }

    public function updateServerStatusListInDb()
    {
        return true;
    }
}