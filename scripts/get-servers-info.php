<?php

require '../autoload.php';

use Project\MysqlDb;
use Project\DbStorage;
use Project\ZabbixApi;

ini_set('display_errors', 1);
$db = new MysqlDb();
$storage = new DbStorage($db);
$api = new ZabbixApi();
$zabbixClass = new \Project\ZabbixClass($api, $storage);
$servers = $zabbixClass->getServerList();

$result = [];
foreach ($servers as $server) {
    $result[] = [
        'host' => $server['host'],
        'status' => $server['status'],
        'priority' => $server['priority'],
        'message' => $server['message'],
    ];
}
unset($servers, $server);

header('Content-type: text/json');
echo json_encode($result);