<?php

namespace Project;

class ZabbixClass implements MonitorInterface {

    protected $storage;
    protected $api;

    public function __construct(ApiInterface $api, StorageInterface $storage)
    {
        $this->api = $api;
        $this->storage = $storage;
    }

    /*protected function getDb() {
        if ($this->db === null) {
            $this->db = new Db();
        }
        return $this->db;
    }

    protected function getApi() {
        if ($this->api === null) {
            $this->api = new ZabbixApi();
        }
        return $this->api;
    }*/

    /**
     * Returns the srever list that stores in storage
     * @return array
     */
    public function getServerList() {
        return $this->storage->getServerList();
    }

    /**
     * Updates the server list in storage from zabbix server
     * @throws \Exception
     */
    public function updateServerListInDb() {
        $api = $this->api;
        $remoteServers = $api->getServerList();

        $servers = $this->storage->getServerList();

        $processedIds = [];

        foreach ($remoteServers as $remoteServer) {
            $found = false;
            foreach ($servers as $server) {
                if ($server['hostid'] == $remoteServer['hostid']) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->storage->updateServerInfo($remoteServer);

            } else {
                $this->storage->addServerInfo($remoteServer);
            }
            $processedIds[] = $remoteServer['hostid'];

        }
        $this->storage->deleteServersNotInList($processedIds);
    }

    /**
     * Updates the server statuses list in storage from zabbix server
     * @throws \Exception
     */
    public function updateServerStatusListInDb() {
        $servers = $this->getServerList();
        $hostids = [];
        foreach ($servers as $server) {
            $hostids[] = $server['hostid'];
        }

        $api = $this->api;
        $statuses = $api->getServerStatuses($hostids);

        $processedIds = [];

        foreach ($statuses as $status) {
            foreach ($servers as $server) {
                if ($server['hostid'] == $status['hostid']) {
                    if ($server['status'] != $status['status']
                        || $server['priority'] != $status['priority']
                        || $server['message'] != $status['message']
                    ) {
                        $this->storage->updateServerStatus($server['hostid'], $status);
                    }

                    $processedIds[] = $server['hostid'];
                    break;
                }
            }
        }

        foreach ($servers as $server) {
            if (!in_array($server['hostid'], $processedIds)) {
                $status = [
                    'status' => 'UNKNOWN',
                    'priority' => '-1',
                    'message' => '',
                ];
                if ($server['status'] != $status['status']
                    || $server['priority'] != $status['priority']
                    || $server['message'] != $status['message']
                ) {
                    $this->storage->updateServerStatus($server['hostid'], $status);
                }

                break;
            }
        }
    }
}