<?php

namespace Project;

class Db {

    protected $connection;

    public function __construct()
    {
        $this->connection = new \mysqli(
            env('DB_HOST'),
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_DATABASE')
        );

        if ($this->connection->connect_error) {
            throw new \Exception('Connect Error (' . $this->connection->connect_errno . ') '
                . $this->connection->connect_error);
        }
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    public function select(array $params)
    {
        if (!isset($params['from'])) {
            throw new \InvalidArgumentException('Incorrect params: '.print_r($params, true));
        }
        if (!isset($params['select'])) {
            $params['select'] = '*';
        }
        $sql = 'SELECT ' . $params['select'] . ' FROM ' . $params['from'];
        if (!empty($params['where'])) {
            $sql .= ' WHERE ' . $params['where'];
        }
        if (!empty($params['postfix'])) {
            $sql .= ' ' . $params['postfix'];
        }
        return $this->query($sql);
    }

    public function selectRow(array $params)
    {
        $row = null;

        if($result = $this->select($params)) {
            $row = $result->fetch_assoc();
        }
        unset($result);
        return $row;
    }

    public function selectArray(array $params)
    {
        $rows = [];

        if($result = $this->select($params)) {
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        unset($row);
        unset($result);
        return $rows;
    }

    public function insert(string $table, array $values) {
        $sql = 'INSERT INTO ' . $table . '(' . implode(', ', array_keys($values)). ') VALUES ';
        $values = array_map(function($value) {
            return '"'. str_replace('"', '\"', $value). '"';
        }, $values);
        $sql .= '(' . implode(', ', $values) . ')';
        return $this->query($sql);
    }

    public function update(string $table, array $values, string $where) {
        $sql = 'UPDATE ' . $table . ' SET ';
        foreach ($values as $key => $value) {
            $values[$key] = $key . '="' . str_replace('"', '\"', $value) . '"';
        }
        $sql .= '' . implode(', ', $values) . '';
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        return $this->query($sql);
    }

    public function delete(string $table, string $where) {
        $sql = 'DELETE FROM ' . $table;
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        return $this->query($sql);
    }
}