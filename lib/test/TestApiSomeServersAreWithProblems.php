<?php
namespace Project\Test;

use Project\ApiInterface;

class TestApiSomeServersAreWithProblems implements ApiInterface {

    public $informationCount = 1;
    public $warningCount = 1;
    public $averageCount = 1;
    public $highCount = 1;
    public $disasterCount = 1;
    public $unreachableCount = 1;

    public function sendRequest($request) {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getServerList()
    {
        $result = [
            ["hostid" => "10132", "host" => "dathomir", "ip" => "176.9.9.211"],
            ["hostid" => "10137", "host" => "dagobah", "ip" => "88.99.99.151"],
            ["hostid" => "10139", "host" => "dantooine", "ip" => "144.76.7.208"],
            ["hostid" => "10149", "host" => "mon-calamari", "ip" => "94.130.89.218"],
            ["hostid" => "10181", "host" => "kamino", "ip" => "144.76.176.8"],
            ["hostid" => "10187", "host" => "apk1", "ip" => "78.46.81.20"],
            ["hostid" => "10188", "host" => "lwhekk", "ip" => "94.130.38.92"],
            ["hostid" => "10228", "host" => "luprora", "ip" => "195.201.82.77"],
            ["hostid" => "10231", "host" => "kpibinom3", "ip" => "195.201.169.150"],
            ["hostid" => "10236", "host" => "ambria", "ip" => "195.201.164.57"]
        ];

        return $result;
    }

    public function getServerStatuses(array $hostids)
    {
        $keys = array_keys($hostids);
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

        $result = [];

        foreach ($hostids as $k => $hostid) {
            if(in_array($k, $unreachableKeys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'UNKNOWN',
                    'priority' => '-1',
                    'message' => 'Test: --\\_(*-*)_/--',
                ];
            } elseif(in_array($k, $informationKeys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'PROBLEM',
                    'priority' => '1',
                    'message' => 'Test: some information',
                ];
            } elseif(in_array($k, $warningKeys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'PROBLEM',
                    'priority' => '2',
                    'message' => 'Test: some warning',
                ];
            } elseif(in_array($k, $averageKeys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'PROBLEM',
                    'priority' => '3',
                    'message' => 'Test: some average',
                ];
            } elseif(in_array($k, $highKeys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'PROBLEM',
                    'priority' => '4',
                    'message' => 'Test: some problem with high priority',
                ];
            } elseif(in_array($k, $disasterKeys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'PROBLEM',
                    'priority' => '5',
                    'message' => 'Test: some disaster',
                ];
            } else {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'OK',
                    'priority' => '-1',
                    'message' => '',
                ];
            }
        }

        return $result;
    }
}