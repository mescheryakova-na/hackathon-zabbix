<?php

namespace Project;

class ZabbixClass {

    protected $db;
    protected $api;

    protected function getDb() {
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
    }

    public function getServerList() {
        $db = $this->getDb();
        return $db->selectArray([
            'from' => 'servers'
        ]);
    }

    public function updateServerListInDb() {
        $api = $this->getApi();
        $remoteServers = $api->getServerList();
        $db = $this->getDb();
        $servers = $this->getServerList();
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
                $db->update('servers', $remoteServer, 'hostid=' . $remoteServer['hostid']);

            } else {
                $db->insert('servers', $remoteServer);
            }
            $processedIds[] = $remoteServer['hostid'];

        }
        if (count($processedIds)) {
            $db->delete('servers', 'hostid NOT IN (' . implode(', ', $processedIds) . ')');
        } else {
            $db->delete('servers', '1=1');
        }
    }

    public function updateServerStatusListInDb() {
        $api = $this->getApi();

        $db = $this->getDb();
        $servers = $this->getServerList();
        $hostids = [];
        foreach ($servers as $server) {
            $hostids[] = $server['hostid'];
        }

        $statuses = $api->getServerStatuses($hostids);

        foreach ($statuses as $status) {
            foreach ($servers as $server) {
                if ($server['hostid'] == $status['hostid']) {
                    if ($server['status'] != $status['status']
                        || $server['priority'] != $status['priority']
                        || $server['message'] != $status['message']
                    ) {
                        $historyItem = $status;
                        $historyItem['date'] = date('Y-m-d H:i:s');

                        $db->insert('history', $historyItem);

                        $db->update('servers', $status, 'hostid=' . $server['hostid']);
                    }
                    break;
                }
            }
        }
    }
}