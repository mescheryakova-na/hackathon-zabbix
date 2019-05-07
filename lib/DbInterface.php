<?php
namespace Project;

interface DbInterface {

    /**
     * @param $sql
     * @return mixed
     */
    public function query($sql);

    /**
     * @param string $table
     * @param array $params
     * @return bool|mixed
     */
    public function select(string $table, array $params);

    /**
     * @param string $table
     * @param array $params
     * @return array|null
     */
    public function selectRow(string $table, array $params);

    /**
     * @param string $table
     * @param array $params
     * @return array
     */
    public function selectArray(string $table, array $params);

    /**
     * @param string $table
     * @param array $values
     * @return bool|mixed
     */
    public function insert(string $table, array $values);

    /**
     * @param string $table
     * @param array $values
     * @param string $where
     * @return bool|mixed
     */
    public function update(string $table, array $values, string $where);

    /**
     * @param string $table
     * @param string $where
     * @return bool|mixed
     */
    public function delete(string $table, string $where);
}