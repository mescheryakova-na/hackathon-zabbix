<?php
namespace Project;

interface ApiInterface {

    /**
     * Send the request to remote server and parses the response
     * @param array $request
     * @return bool|array
     */
    public function sendRequest($request);

    /**
     * Returns the list of the active servers/hosts
     * @return array
     */
    public function getServerList();

    /**
     * Gets the servers/hosts statuses
     * @param array $hostids
     * @return array
     */
    public function getServerStatuses(array $hostids);
}
