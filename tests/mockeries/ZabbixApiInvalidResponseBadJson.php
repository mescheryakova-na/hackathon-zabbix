<?php

namespace Tests\Mockeries;

class ZabbixApiInvalidResponseBadJson extends \Project\ZabbixApi
{
    protected function sendCurlRequest($request)
    {
        return ['{"jsonrpc":"2.0","result":"5b56eee8be445e98f0bd42b435736e42,"id":"1"}', 200];
    }
}