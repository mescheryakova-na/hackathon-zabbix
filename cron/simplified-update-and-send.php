#!/usr/bin/php
<?php

use Project\ZabbixApi;

require __DIR__ . '/../autoload.php';

$api = new ZabbixApi();

$servers = $api->getServerList();
$hostids = [];
foreach ($servers as $server) {
    $hostids[] = $server['hostid'];
}

$priorityColorMap = [
    -1 => ['red' => 0, 'green' => 170, 'blue' => 24], //'#00AA18',
    0 => ['red' => 75, 'green' => 75, 'blue' => 75], //'#4B4B4B',
    1 => ['red' => 2, 'green' => 117, 'blue' => 255], //'#0275FF',
    2 => ['red' => 255, 'green' => 246, 'blue' => 0], //'#FFF600',
    3 => ['red' => 255, 'green' => 87, 'blue' => 34], //'#ff5722',
    4 => ['red' => 255, 'green' => 0, 'blue' => 0], //'#FF0000',
    5 => ['red' => 129, 'green' => 0, 'blue' => 4], //'#810004',
];

$unknownColor = ['red' => 75, 'green' => 75, 'blue' => 75]; //#4B4B4B;

$serverDiodMap = [
    '10132' => 0, //dathomir
    '10137' => 1, //dagobah
    '10139' => 2, //dantooine
    '10149' => 3, //mon-calamari
    '10181' => 4, //kamino
    '10187' => 5, //apk1
    '10188' => 6, //lwhekk
    '10228' => 7, //luprora
    '10231' => 8, //kpibinom3
    '10236' => 9, //ambria
];


$startTime = microtime(true);

while (true) {
    $iterationStartTime = microtime(true);

    $statuses = $api->getServerStatuses($hostids);

    $result = [];
    foreach ($statuses as $server) {
        $result[] = [
            'id' => (isset($serverDiodMap[$server['hostid']])
                ? $serverDiodMap[$server['hostid']]
                : $server['hostid']),
            //'host' => $server['host'],
            'colors' => (($server['status'] == 'UNKNOWN')
                ? $unknownColor
                : [$priorityColorMap[$server['priority']]]),
            'time' => 0,
        ];
    }
    unset($servers, $server);

    $raspberryUrl = env('RASPBERRY_URL');
    /*header('Content-type: text/json');*/
    echo json_encode($result);


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $raspberryUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($result));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    /*$response = curl_exec($ch);

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);*/

    var_dump($code);

    $iterationDuration = microtime(true) - $iterationStartTime;

    if($iterationDuration < 10) {
        usleep(floor((10 - $iterationDuration) * 1000000));
    }

    if (microtime(true) - $startTime >= 60) {
        break;
    }
}
