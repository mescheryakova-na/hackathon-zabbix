<?php

use PHPUnit\Framework\TestCase;
use Project\DbStorage;
use Tests\Mockeries\TestMysqlDb;

class DbStorageTest extends TestCase
{
    protected $db;
    protected $storage;
    protected $dumpDbData;

    protected function setUp(): void
    {
        $this->db = new TestMysqlDb();
        $this->storage = new DbStorage($this->db);

        $this->dumpDbData = $this->db->selectArray('servers', []);
        $this->db->delete('servers', '1=1');

        $this->db->insert('servers', [
            'hostid' => 10132,
            'host' => 'dathomir',
            'ip' => '176.9.9.211',
            'status' => 'PROBLEM',
            'status' => 'Lack of free swap space on dathomir',
            'priority' => '2',
        ]);

        $this->db->insert('servers', [
            'hostid' => 10137,
            'host' => 'dagobah',
            'ip' => '88.99.99.151',
            'status' => 'PROBLEM',
            'status' => 'Lack of free swap space on dagobah',
            'priority' => '2',
        ]);

        $this->db->insert('servers', [
            'hostid' => 10139,
            'host' => 'dantooine',
            'ip' => '144.76.7.208',
            'status' => 'OK',
            'status' => '',
            'priority' => '-1',
        ]);

        $this->db->insert('servers', [
            'hostid' => 10149,
            'host' => 'mon-calamari',
            'ip' => '94.130.89.218',
            'status' => 'OK',
            'status' => '',
            'priority' => '-1',
        ]);

        $this->db->insert('servers', [
            'hostid' => 10231,
            'host' => 'kpibinom3',
            'ip' => '195.201.169.150',
            'status' => 'PROBLEM',
            'status' => 'Zabbix agent on kpibinom3 is unreachable for 5 minutes',
            'priority' => '4',
        ]);
    }

    protected function tearDown() : void
    {
        $this->db->delete('servers', '1=1');

        foreach ($this->dumpDbData as $server) {
            $this->db->insert('servers', $server);
        }
    }

    public function testGetServerList()
    {
        $data = $this->storage->getServerList();
        $this->assertIsArray($data);
    }

    /**
     * @depends testGetServerList
     */
    public function testUpdateServerInfo()
    {
        $data = $this->storage->getServerList();

        $index = rand(0, count($data) - 1);

        $oldData = [
            'message' => $data[$index]['message'],
            'hostid' => $data[$index]['hostid'],
        ];
        $result = $this->storage->updateServerInfo([
            'hostid' => $data[$index]['hostid'],
            'message' => 'test message'
        ]);
        $this->assertTrue($result);

        $result = $this->storage->updateServerInfo([
            'hostid' => 99999,
            'message' => 'test message 2'
        ]);
        $this->assertTrue($result);

        $data = $this->storage->getServerList();

        foreach ($data as $k => $line) {
            if ($k == $index) {
                $this->assertSame($line['message'], 'test message');
            }
            $this->assertNotSame($line['message'], 'test message 2');
        }

        $result = $this->storage->updateServerInfo([
            'hostid' => $oldData['hostid'],
            'message' => $oldData['message']
        ]);
        $this->assertTrue($result);
    }

    /**
     * @depends testUpdateServerInfo
     */
    public function testIncorrectUpdateServerInfo()
    {
        $this->expectException(\InvalidArgumentException::class);
        $result = $this->storage->updateServerInfo([]);
    }

    /**
     * @depends testGetServerList
     */
    public function testAddServerInfo()
    {
        $result = $this->storage->addServerInfo([
            'hostid' => 88888,
            'host' => 'test_server',
            'ip' => '171.55.66.88',
            'status' => 'OK'
        ]);
        $this->assertTrue($result);

        $data = $this->storage->getServerList();

        foreach ($data as $k => $line) {
            if ($line['hostid'] == 88888) {
                $this->assertSame($line['host'], 'test_server');
                $this->assertSame($line['ip'], '171.55.66.88');
                $this->assertSame($line['status'], 'OK');
                break;
            }
        }

        $this->db->delete('servers', 'hostid=88888');
    }

    /**
     * @depends testAddServerInfo
     */
    public function testIncorrectAddServerInfo()
    {
        $result = $this->storage->addServerInfo([
            'hostid' => 88888,
            'host2' => 'test_server',
            'ip' => '171.55.66.88',
            'status' => 'OK'
        ]);
        $this->assertFalse($result);

        $data = $this->storage->getServerList();

        foreach ($data as $k => $line) {
            $this->assertNotSame(intval($line['hostid']), 88888);
        }
    }

    /**
     * @depends testGetServerList
     * @depends testAddServerInfo
     */
    public function testDeleteServersNotInList()
    {
        $data = $this->storage->getServerList();

        $hostids = [];
        foreach ($data as $line) {
            $hostids[] = $line['hostid'];
        }

        $result = $this->storage->addServerInfo([
            'hostid' => 89898,
            'host2' => 'test_server',
            'ip' => '171.55.66.88',
            'status' => 'OK'
        ]);

        $this->storage->deleteServersNotInList($hostids);

        $data = $this->storage->getServerList();

        foreach ($data as $k => $line) {
            $this->assertNotSame(intval($line['hostid']), 89898);
        }
    }

    /**
     * @depends testGetServerList
     */
    public function testUpdateServerStatus()
    {
        $data = $this->storage->getServerList();

        $index = rand(0, count($data) - 1);

        $historyLastId = $this->db->selectRow('history', [
            'postfix' => 'ORDER BY id DESC'
        ]);
        $historyLastId = $historyLastId['id'];

        $oldStatus = [
            'status' => $data[$index]['status'],
            'message' => $data[$index]['message'],
            'priority' => $data[$index]['priority'],
            'hostid' => $data[$index]['hostid'],
        ];
        $result = $this->storage->updateServerStatus($data[$index]['hostid'], [
            'status' => 'TEST_STATUS',
            'message' => 'test message',
            'priority' => '0',
            'hostid' => $data[$index]['hostid'],
        ]);

        $this->assertTrue($result);

        $data = $this->storage->getServerList();

        foreach ($data as $k => $line) {
            if ($line['hostid'] == $oldStatus['hostid']) {
                $this->assertSame($line['status'], 'TEST_STATUS');
                $this->assertSame($line['message'], 'test message');
                $this->assertSame(intval($line['priority']), 0);
                break;
            }
        }

        $historyItem = $this->db->selectRow('history', [
            'where' => 'hostid=' . $oldStatus['hostid'] . ' AND id >' . $historyLastId
        ]);
        $this->assertNotEmpty($historyItem);
        $this->assertSame($historyItem['status'], 'TEST_STATUS');
        $this->assertSame($historyItem['message'], 'test message');
        $this->assertSame(intval($historyItem['priority']), 0);

        $result = $this->storage->updateServerStatus($oldStatus['hostid'], $oldStatus);

        $this->assertTrue($result);
    }

    /**
     * @depends testGetServerList
     */
    public function testIncorrectUpdateServerStatus()
    {
        $data = $this->storage->getServerList();

        $index = rand(0, count($data) - 1);

        $historyLastId = $this->db->selectRow('history', [
            'postfix' => 'ORDER BY id DESC'
        ]);
        $historyLastId = $historyLastId['id'];

        $oldStatus = [
            'status' => $data[$index]['status'],
            'message' => $data[$index]['message'],
            'priority' => $data[$index]['priority'],
            'hostid' => $data[$index]['hostid'],
        ];
        $result = $this->storage->updateServerStatus($data[$index]['hostid'], [
            'status2' => 'TEST_STATUS',
            'message' => 'test message',
            'priority' => '0',
            'hostid' => $data[$index]['hostid'],
        ]);

        $this->assertFalse($result);

        $historyItem = $this->db->selectRow('history', [
            'where' => 'hostid=' . $oldStatus['hostid'] . ' AND id >' . $historyLastId
        ]);
        $this->assertEmpty($historyItem);
    }
}