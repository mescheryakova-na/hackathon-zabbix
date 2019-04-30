<?php
namespace Project;

class DbStorage implements StorageInterface {

    protected $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function getServerList()
    {
        return $this->db->selectArray([
            'from' => 'servers'
        ]);
    }

    public function updateServerInfo(array $data)
    {
        if (empty($data['hostid'])) {
            throw new \InvalidArgumentException('');
        }
        return ($this->db->update('servers', $data, 'hostid=' . $data['hostid']) !== false);
    }

    public function addServerInfo(array $data)
    {
        return ($this->db->insert('servers', $data) !== false);
    }

    public function deleteServersNotInList($ids)
    {
        if (count($ids)) {
            $this->db->delete('servers', 'hostid NOT IN (' . implode(', ', $ids) . ')');
        } else {
            $this->db->delete('servers', '1=1');
        }
    }

    public function updateServerStatus($hostid, array $status)
    {
        $historyItem = $status;
        $historyItem['date'] = date('Y-m-d H:i:s');

        $this->db->insert('history', $historyItem);

        $this->db->update('servers', $status, 'hostid=' . $hostid);
    }
}