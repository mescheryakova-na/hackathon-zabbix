#!/usr/bin/php
<?php

require __DIR__ . '/../autoload.php';

use Project\MysqlDb;
use Project\DbStorage;
use Project\ZabbixApi;

$db = new MysqlDb();
$storage = new DbStorage($db);
$api = new ZabbixApi();
$zabbixClass = new \Project\ZabbixClass($api, $storage);
$zabbixClass->updateServerListInDb();

