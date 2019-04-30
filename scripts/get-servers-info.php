<?php

require '../autoload.php';

use Project\Db;
use Project\ZabbixApi;
ini_set('display_errors', 1);
$db = new Db();
$api = new ZabbixApi();
$zabbixClass = new \Project\ZabbixClass($api, $db);
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