<?php
namespace Project\Test;

use Project\ApiInterface;
use Project\DbInterface;
use Project\MonitorInterface;

class TestRemoteServerIsUnreachable implements MonitorInterface {

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
            $result[$k]['status'] = 'UNKNOWN';
            $result[$k]['priority'] = '-1';
            $result[$k]['message'] = 'Test: server is unreachable';
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