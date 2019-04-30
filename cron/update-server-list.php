#!/usr/bin/php
<?php

require __DIR__ . '/../autoload.php';

use Project\Db;
use Project\ZabbixApi;

$db = new Db();
$api = new ZabbixApi();
$zabbixClass = new \Project\ZabbixClass($api, $db);
$zabbixClass->updateServerListInDb();

