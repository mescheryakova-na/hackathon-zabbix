<?php

namespace Tests\Mockeries;

class ZabbixApiIncorrectResponse extends \Project\ZabbixApi
{
    protected function sendCurlRequest($request)
    {
        return [
            json_encode([
                'jsonrpc' => '2.0',
                'noresult' => '5b56eee8be445e98f0bd42b435736e42',
                'id' => '1',
            ]),
            200
        ];
    }
}