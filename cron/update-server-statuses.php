#!/usr/bin/php
<?php

require __DIR__ . '/../autoload.php';

use Project\MysqlDb;
use Project\ZabbixApi;
use Project\DbStorage;

$db = new MysqlDb();
$storage = new DbStorage($db);
$api = new ZabbixApi();
$zabbixClass = new \Project\ZabbixClass($api, $storage);
$zabbixClass->updateServerStatusListInDb();

