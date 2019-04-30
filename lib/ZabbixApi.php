<?php

namespace Project;

class ZabbixApi implements ApiInterface{

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string|null
     */
    protected $token = null;
    /*protected $priorities = [
        0 => 'not classified',
        1 => 'information',
        2 => 'warning',
        3 => 'average',
        4 => 'high',
        5 => 'disaster',
    ];*/

    public function __construct()
    {
        $this->url = env('ZABBIX_URL');
    }

    /**
     * Send the request to zabbix server and parses the response
     * @param array $request
     * @return bool|array
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function sendRequest($request)
    {
        if (!extension_loaded('curl')) {
            throw new \Exception('The class needs the curl extension for performance.');
        }

        if (!is_array($request)) {
            throw new \InvalidArgumentException('The request must be an array');
        }

        $logger = new LogWriter('_logs/requests/' . date('Ymd') . '.log');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch,CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            $logger->error('Incorrect response code.'
                .' Request: '. print_r($request, true)
                .' Response: '. $response
                .' Code: '. curl_getinfo($ch, CURLINFO_HTTP_CODE)
            );
            return false;
        }

        try {
            $response = json_decode($response, true);
        } catch (\Exception $e) {
            $logger->error('Incorrect response.'
                .' Request: '. print_r($request, true)
                .' Response: '. $response
            );
            throw new \Exception('The remote server gave the incorrect response');
        }

        if (!isset($response['result'])) {
            $logger->error('Bad response.'
                .' Request: '. print_r($request, true)
                .' Response: '. print_r($response, true)
            );
            throw new \Exception('The remote server gave the bad response');
        }

        $logger->info('Request: '. print_r($request, true)
            .' Response: '. print_r($response, true)
        );

       return $response;
    }

    /**
     * Gets the authorization token
     * @return string|null
     * @throws \Exception
     */
    public function getAuthToken() {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'user.login',
            'params' => [
                'user' => env('ZABBIX_USERNAME'),
                'password' => env('ZABBIX_PASSWORD'),
            ],
            'id' => 1,
            'auth' => $this->token,
        ];

        if($response = $this->sendRequest($request)) {
            $this->token = $response['result'];
        }
        return $this->token;
    }

    /**
     * Returns the list of the active servers/hosts
     * @return array
     * @throws \Exception
     */
    public function getServerList() {

        $token = $this->getAuthToken();
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'host.get',
            'params' => [
                'output' => [
                    'hostid',
                    'host',
                ],
                'selectInterfaces' => [
                    "ip"
                ],
            ],
            'id' => 2,
            'auth' => $token,
        ];
        $response = $this->sendRequest($request);
        $result = [];
        foreach ($response['result'] as $server) {
            $result[$server['hostid']] = [
                'hostid' => $server['hostid'],
                'host' => $server['host'],
                'ip' => $server['interfaces'][0]['ip'],
            ];
        }
        return $result;
    }

    /**
     * Gets the servers/hosts statuses
     * @param array $hostids
     * @return array
     * @throws \Exception
     */
    public function getServerStatuses(array $hostids)
    {
        $token = $this->getAuthToken();
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'problem.get',
            'params' => [
                'output' => "extend",
                'selectAcknowledges' => "extend",
                'selectTags' => "extend",
                'recent' => false,
                'hostids' => $hostids,
            ],
            'id' => 3,
            'auth' => $token,
        ];
        $response = $this->sendRequest($request);

        $eventIds = [];
        foreach ($response['result'] as $problem) {
            $eventIds[] = $problem['eventid'];
        }

        $request = [
            'jsonrpc' => '2.0',
            'method' => 'event.get',
            'params' => [
                'output' => 'extend',
                'select_acknowledges' => 'extend',
                'select_alerts' => 'extend',
                'selectRelatedObject' => 'extend',
                'selectHosts' => 'extend',
                'eventids' => $eventIds,
            ],
            'id' => 4,
            'auth' => $token,
        ];
        $response = $this->sendRequest($request);

        $result = [];
        foreach ($hostids as $hostid) {
            $result[$hostid] = ['hostid' => $hostid, 'status' => 'OK', 'message' => '', 'priority' => -1];
        }
        foreach ($response['result'] as $event) {

            if ($event['relatedObject']['status'] == 0
                && !$event['relatedObject']['templateid'] == 0) {
                foreach ($event['hosts'] as $host) {
                    if (isset($result[$host['hostid']])) {
                        if ($result[$host['hostid']]['priority'] < $event['relatedObject']['priority']) {
                            $result[$host['hostid']]['status'] = 'PROBLEM';
                            $result[$host['hostid']]['priority'] = $event['relatedObject']['priority'];
                            $result[$host['hostid']]['message'] = str_replace(
                                ['{HOST.NAME}'],
                                [$host['name']],
                                $event['relatedObject']['description']
                            );
                        }
                    }
                }
            }
        }

        return $result;
    }
}