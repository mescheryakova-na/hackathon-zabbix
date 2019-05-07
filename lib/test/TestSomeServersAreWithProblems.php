<?php
namespace Project\Test;

use Project\ApiInterface;
use Project\DbInterface;
use Project\MonitorInterface;

class TestSomeServersAreWithProblems implements MonitorInterface {

    protected $db;
    protected $api;

    public $informationCount = 1;
    public $warningCount = 1;
    public $averageCount = 1;
    public $highCount = 1;
    public $disasterCount = 1;
    public $unreachableCount = 1;

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

        $keys = array_keys($result);
        shuffle($keys);

        $unreachableKeys = array_slice($keys, 0, $this->unreachableCount);
        $keys = array_diff($keys, $unreachableKeys);

        $informationKeys = array_slice($keys, 0, $this->informationCount);
        $keys = array_diff($keys, $informationKeys);

        $warningKeys = array_slice($keys, 0, $this->warningCount);
        $keys = array_diff($keys, $warningKeys);

        $averageKeys = array_slice($keys, 0, $this->averageCount);
        $keys = array_diff($keys, $averageKeys);

        $highKeys = array_slice($keys, 0, $this->highCount);
        $keys = array_diff($keys, $highKeys);

        $disasterKeys = array_slice($keys, 0, $this->disasterCount);
        $keys = array_diff($keys, $disasterKeys);

        foreach ($result as $k => $server) {
            if(in_array($k, $unreachableKeys)) {
                $result[$k]['status'] = 'UNKNOWN';
                $result[$k]['priority'] = '-1';
                $result[$k]['message'] = 'Test: --\\_(*-*)_/--';
            } elseif(in_array($k, $informationKeys)) {
                $result[$k]['status'] = 'PROBLEM';
                $result[$k]['priority'] = '1';
                $result[$k]['message'] = 'Test: some information';
            } elseif(in_array($k, $warningKeys)) {
                $result[$k]['status'] = 'PROBLEM';
                $result[$k]['priority'] = '2';
                $result[$k]['message'] = 'Test: some warning';
            } elseif(in_array($k, $averageKeys)) {
                $result[$k]['status'] = 'PROBLEM';
                $result[$k]['priority'] = '3';
                $result[$k]['message'] = 'Test: some average';
            } elseif(in_array($k, $highKeys)) {
                $result[$k]['status'] = 'PROBLEM';
                $result[$k]['priority'] = '4';
                $result[$k]['message'] = 'Test: some problem with high priority';
            } elseif(in_array($k, $disasterKeys)) {
                $result[$k]['status'] = 'PROBLEM';
                $result[$k]['priority'] = '5';
                $result[$k]['message'] = 'Test: some disaster';
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