<?php

use PHPUnit\Framework\TestCase;
use Project\DbStorage;
use Tests\Mockeries\TestMysqlDb;
use Project\ZabbixApi;
use Project\ZabbixClass;

class ZabbixClassTest extends TestCase
{

    protected $zabbixClass;
    protected $db;

    protected function setUp(): void
    {
        $this->db = new TestMysqlDb();
        $storage = new DbStorage($this->db);
        $api = new ZabbixApi();
        $this->zabbixClass = new ZabbixClass($api, $storage);
    }

    public function testUpdateServerListInDb()
    {
        $this->db->query('TRUNCATE TABLES servers');

        $this->zabbixClass->updateServerListInDb();
        $count = intval($this->db->query('SELECT COUNT(hostid) FROM servers')->fetch_row()[0]);
        $this->assertGreaterThan(0, $count);
    }

    /**
     * @depends testUpdateServerListInDb
     */
    public function testGetServerList()
    {
        $data = $this->zabbixClass->getServerList();
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
        foreach ($data as $line) {
            $this->assertArrayHasKey('hostid', $line);
            $this->assertArrayHasKey('host', $line);
            $this->assertArrayHasKey('ip', $line);
            $this->assertArrayHasKey('status', $line);
            $this->assertArrayHasKey('priority', $line);
        }
    }

    /**
     * @depends testUpdateServerListInDb
     * @depends testGetServerList
     */
    public function testUpdateServerStatusListInDb()
    {
        $this->db->query('UPDATE servers SET priority = -2, status="UNKNOWN", message=""');

        $this->zabbixClass->updateServerStatusListInDb();

        $data = $this->zabbixClass->getServerList();
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
        foreach ($data as $line) {
            $this->assertArrayHasKey('hostid', $line);
            $this->assertArrayHasKey('host', $line);
            $this->assertArrayHasKey('ip', $line);
            $this->assertArrayHasKey('status', $line);
            $this->assertArrayHasKey('priority', $line);
            $this->assertGreaterThanOrEqual(-1, $line['priority']);
            $this->assertLessThanOrEqual(5, $line['priority']);
            $this->assertRegExp("/OK|PROBLEM/si", $line['status']);
        }
    }
}