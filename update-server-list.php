#!/usr/bin/php
<?php
require './autoload.php';

$zabbixClass = new \Project\ZabbixClass();
$zabbixClass->updateServerListInDb();

