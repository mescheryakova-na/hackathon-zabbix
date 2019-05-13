<?php
namespace Project\Test;

use Project\ApiInterface;

class TestApiSomeServersAreWithHighs implements ApiInterface {

    public $count = 3;
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
        $keys = array_slice($keys, 0, $this->count);

        $result = [];

        foreach ($hostids as $k => $hostid) {
            if(in_array($k, $keys)) {
                $result[] = [
                    'hostid' => $hostid,
                    'status' => 'PROBLEM',
                    'priority' => '4',
                    'message' => 'Test: some problem with high priority',
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