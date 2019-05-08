#!/usr/bin/php
<?php

use Project\DbStorage;
use Project\MysqlDb;
use Project\ZabbixApi;
use Project\ZabbixClass;

require __DIR__ . '/../autoload.php';

$db = new MysqlDb();
$storage = new DbStorage($db);
$api = new ZabbixApi();
$zabbixClass = new ZabbixClass($api, $storage);
$servers = $zabbixClass->getServerList();

$priorityColorMap = [
    -1 => ['red' => 0, 'green' => 170, 'blue' => 24], //'#00AA18',
    0 => ['red' =>75, 'green' => 75, 'blue' => 75], //'#4B4B4B',
    1 => ['red' => 2, 'green' => 117, 'blue' => 255], //'#0275FF',
    2 => ['red' => 255, 'green' => 246, 'blue' => 0], //'#FFF600',
    3 => ['red' => 255, 'green' => 87, 'blue' => 34], //'#ff5722',
    4 => ['red' => 255, 'green' => 0, 'blue' => 0], //'#FF0000',
    5 => ['red' => 129, 'green' => 0, 'blue' => 4], //'#810004',
];

$result = [];
foreach ($servers as $server) {
    $result[] = [
        'id' => $server['diod_id'],
        //'host' => $server['host'],
        'colors' => [$priorityColorMap[$server['priority']]],
        'time' => 0,
    ];
}
unset($servers, $server);

$raspberryUrl = env('RASPBERRY_URL');
/*header('Content-type: text/json');
echo json_encode($result);*/

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $raspberryUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch,CURLOPT_POSTFIELDS, $result);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

$response = curl_exec($ch);

$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

var_dump($code);