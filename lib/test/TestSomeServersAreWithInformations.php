<?php
namespace Project\Test;

use Project\ApiInterface;
use Project\DbInterface;
use Project\MonitorInterface;

class TestSomeServersAreWithInformations implements MonitorInterface {

    protected $db;
    protected $api;

    public $count = 3;

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

        $keys = array_keys($result);
        shuffle($keys);
        $keys = array_slice($keys, 0, $this->count);
        foreach ($result as $k => $server) {
            if(in_array($k, $keys)) {
                $result[$k]['status'] = 'PROBLEM';
                $result[$k]['priority'] = '1';
                $result[$k]['message'] = 'Test: some information';
            } else {
                $result[$k]['status'] = 'OK';
                $result[$k]['priority'] = '-1';
                $result[$k]['message'] = '';
            }
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