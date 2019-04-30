<?php

namespace Project;

class ZabbixClass implements MonitorInterface {

    protected $db;
    protected $api;

    public function __construct(ApiInterface $api, DbInterface $db)
    {
        $this->api = $api;
        $this->db = $db;
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
     * Returns the srever list that stores in db
     * @return array
     */
    public function getServerList() {
        $db = $this->db;
        return $db->selectArray([
            'from' => 'servers'
        ]);
    }

    /**
     * Updates the server list in db from zabbix server
     * @throws \Exception
     */
    public function updateServerListInDb() {
        $api = $this->api;
        $remoteServers = $api->getServerList();

        $db = $this->db;
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

    /**
     * Updates the server statuses list in db from zabbix server
     * @throws \Exception
     */
    public function updateServerStatusListInDb() {
        $db = $this->db;
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
                        $historyItem = $status;
                        $historyItem['date'] = date('Y-m-d H:i:s');

                        $db->insert('history', $historyItem);

                        $db->update('servers', $status, 'hostid=' . $server['hostid']);
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