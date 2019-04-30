<?php
namespace Project\Test;

use Project\ApiInterface;
use Project\DbInterface;
use Project\MonitorInterface;

class TestEverythingIsBroken implements MonitorInterface {

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
        $result = $db->selectArray([
            'from' => 'servers'
        ]);

        foreach ($result as $k => $server) {
            $result[$k]['status'] = 'PROBLEM';
            $result[$k]['priority'] = '5';
            $result[$k]['message'] = 'Test: we are going to die!!!';
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