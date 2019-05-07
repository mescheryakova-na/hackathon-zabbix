<?php

namespace Tests\Mockeries;

class ZabbixApiInvalidResponse extends \Project\ZabbixApi
{
    protected function sendCurlRequest($request)
    {
        return ['something strange', 200];
    }
}