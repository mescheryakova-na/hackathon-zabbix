<?php

namespace Tests\Mockeries;

class TestMysqlDb extends \Project\MysqlDb
{
    public function __construct()
    {
        $this->connection = new \mysqli(
            env('DB_HOST'),
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            'test_hackathon'
        );

        if ($this->connection->connect_error) {
            throw new \Exception('Connect Error (' . $this->connection->connect_errno . ') '
                . $this->connection->connect_error);
        }
    }
}