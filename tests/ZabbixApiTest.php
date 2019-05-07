<?php

use PHPUnit\Framework\TestCase;
use Project\ZabbixApi;

class ZabbixApiTest extends TestCase
{

    protected $api;

    protected function setUp(): void
    {
        $this->api = new ZabbixApi();
    }

    public function testApiVersion()
    {
        $this->assertSame($this->api->getApiVersion(), '3.4.7');
    }

    /**
     * @depends testApiVersion
     */
    public function testGetAuthToken()
    {
        $token = $this->api->getAuthToken();
        $this->assertNotEmpty($token);
        $this->assertStringMatchesFormat('%x', $token);
        $this->assertSame(strlen($token), 32);
    }

    /**
     * @depends testApiVersion
     * @depends testGetAuthToken
     */
    public function testGetServerList()
    {
        $data = $this->api->getServerList();
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
        foreach ($data as $line) {
            $this->assertArrayHasKey('hostid', $line);
            $this->assertArrayHasKey('host', $line);
            $this->assertArrayHasKey('ip', $line);
        }
    }

    /**
     * @depends testApiVersion
     * @depends testGetAuthToken
     * @depends testGetServerList
     */
    public function testGetServerStatuses()
    {
        $servers = $this->api->getServerList();
        $hostids = [];
        foreach ($servers as $server) {
            $hostids[] = $server['hostid'];
        }

        $data = $this->api->getServerStatuses($hostids);
        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
        foreach ($data as $k => $line) {
            $this->assertContains($k, $hostids);
            $this->assertArrayHasKey('priority', $line);
            $this->assertArrayHasKey('status', $line);
            $this->assertArrayHasKey('message', $line);
        }
    }

    public function testIncorrectRequest1()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->api->sendRequest('bad request');
    }

    public function testIncorrectRequest2()
    {
        $this->expectException(\InvalidArgumentException::class);

        $request = new stdClass();
        $request->method = 'user.login';
        $this->api->sendRequest($request);
    }

    public function testUnknownMethod()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The remote server gave the incorrect response');

        $token = $this->api->getAuthToken();
        $this->api->sendRequest([
            'jsonrpc' => '2.0',
            'method' => 'unknown.method',
            'token' => $token
        ]);
    }

    public function testBadResponseCode()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The remote server gave the bad response code');

        $stub = new \Tests\Mockeries\ZabbixApiInvalidResponseCode();
        $stub->sendRequest([]);
    }

    public function testBadResponseDataType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The remote server gave the incorrect response');

        $stub = new \Tests\Mockeries\ZabbixApiInvalidResponse();
        $stub->sendRequest([]);
    }

    public function testBadResponseDataType2()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The remote server gave the incorrect response');

        $stub = new \Tests\Mockeries\ZabbixApiInvalidResponseBadJson();
        $stub->sendRequest([]);
    }

    public function testBadResponseFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The remote server gave the bad response');

        $stub = new \Tests\Mockeries\ZabbixApiIncorrectResponse();
        $stub->sendRequest([]);
    }

    public function testRequestWithBadToken()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The remote server gave the incorrect response');

        $this->api->sendRequest([
            'jsonrpc' => '2.0',
            'method' => 'user.login',
            'token' => '1234567890'
        ]);
    }
}