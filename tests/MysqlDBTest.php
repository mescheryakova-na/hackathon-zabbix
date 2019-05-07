<?php

use PHPUnit\Framework\TestCase;
use Tests\Mockeries\TestMysqlDb;

class MysqlDBTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        if ($this->getName() != 'testConnection') {
            $this->db = new TestMysqlDb();
        }
    }

    protected function tearDown(): void
    {
        if ($this->getName() != 'testConnections') {
            $this->db->query('DROP TABLE IF EXISTS test');
        }
    }

    public function testConnection()
    {
        $this->db = new TestMysqlDb();
        $this->assertInstanceOf(TestMysqlDb::class, $this->db);
    }

    /**
     * @depends testConnection
     */
    public function testQuery()
    {
        $result = $this->db->query('SHOW DATABASES;');
        $this->assertInstanceOf(mysqli_result::class, $result);
    }

    /**
     * @depends testConnection
     */
    public function testIncorrectQuery()
    {
        $result = $this->db->query('SOME INCORRECT QUERY;');
        $this->assertFalse($result);
    }

    /**
     * @depends testQuery
     */
    public function testSelect()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter"),
            (3, "Jack")
            ;'
        );

        $result = $this->db->select('test', []);
        $this->assertInstanceOf(mysqli_result::class, $result);
        $this->assertEquals($result->num_rows, 3);

        $result = $this->db->select('test', ['where' => 'id=1']);
        $this->assertInstanceOf(mysqli_result::class, $result);
        $this->assertEquals($result->num_rows, 1);

        $result = $this->db->select('test', ['where' => 'id=4']);
        $this->assertInstanceOf(mysqli_result::class, $result);
        $this->assertEquals($result->num_rows, 0);

        $result = $this->db->select('test', ['where' => 'id>1', 'postfix' => 'ORDER BY id DESC']);
        $this->assertInstanceOf(mysqli_result::class, $result);
        $this->assertEquals($result->num_rows, 2);

        $start = 4;
        while ($row = $result->fetch_assoc()) {
            $this->assertLessThan($start, $row['id']);
            $start = $row['id'];
        }

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     */
    public function testSelectWithIncorrectParams()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter"),
            (3, "Jack")
            ;'
        );

        $result = $this->db->select('test', ['where' => 'id=']);
        $this->assertFalse($result);

        $result = $this->db->select('test', ['where' => 'id>1', 'postfix' => 'ORDER Y id DESC']);
        $this->assertFalse($result);

        $this->expectException(\InvalidArgumentException::class);

        $this->db->select('', []);

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testSelect
     */
    public function testSelectRow()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter"),
            (3, "Jack")
            ;'
        );

        $result = $this->db->selectRow('test', ['where' => 'id=1']);
        $this->assertIsArray($result);
        $this->assertSame($result['id'], '1');
        $this->assertSame($result['name'], 'John');

        $result = $this->db->selectRow('test', ['where' => 'id=3']);
        $this->assertIsArray($result);
        $this->assertSame($result['id'], '3');
        $this->assertSame($result['name'], 'Jack');

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testSelect
     */
    public function testSelectArray()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter"),
            (3, "Jack")
            ;'
        );

        $result = $this->db->selectArray('test', ['where' => 'id=1 OR id=3', 'postfix' => 'ORDER BY id ASC']);
        $this->assertIsArray($result);

        $this->assertSame($result[0]['id'], '1');
        $this->assertSame($result[0]['name'], 'John');

        $this->assertSame($result[1]['id'], '3');
        $this->assertSame($result[1]['name'], 'Jack');

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     * @depends testSelectArray
     */
    public function testInsert()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 0);

        $this->db->insert('test', ['id' => 1, 'name' => 'John']);
        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 1);

        $this->db->insert('test', ['id' => 2, 'name' => 'Peter']);
        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->selectArray('test', ['postfix' => 'ORDER BY id ASC']);
        $this->assertIsArray($result);

        $this->assertSame($result[0]['id'], '1');
        $this->assertSame($result[0]['name'], 'John');

        $this->assertSame($result[1]['id'], '2');
        $this->assertSame($result[1]['name'], 'Peter');

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     * @depends testSelectArray
     */
    public function testIncorrectInsert()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 0);

        $result = $this->db->insert('test', ['id' => 1, 'unknown_column' => 'John']);
        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 0);
        $this->assertFalse($result);

        $result = $this->db->insert('test2', ['id' => 1, 'unknown_column' => 'John']);
        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 0);
        $this->assertFalse($result);

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     * @depends testSelectArray
     */
    public function testUpdate()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter")
            ;'
        );

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->update('test', ['name' => 'Kate'], 'id=2');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->update('test', ['name' => 'Kate'], 'id=3');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->selectArray('test', ['postfix' => 'ORDER BY id ASC']);
        $this->assertIsArray($result);

        $this->assertSame($result[0]['id'], '1');
        $this->assertSame($result[0]['name'], 'John');

        $this->assertSame($result[1]['id'], '2');
        $this->assertSame($result[1]['name'], 'Kate');

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     * @depends testSelectArray
     */
    public function testIncorrectUpdate()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter")
            ;'
        );

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

       $result = $this->db->update('test', ['unknown_column' => 'Kate'], 'id=2');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);
        $this->assertFalse($result);

        $result = $this->db->update('test2', ['name' => 'Kate'], 'id=2');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);
        $this->assertFalse($result);

        $result = $this->db->selectArray('test', ['postfix' => 'ORDER BY id ASC']);
        $this->assertIsArray($result);

        $this->assertSame($result[0]['id'], '1');
        $this->assertSame($result[0]['name'], 'John');

        $this->assertSame($result[1]['id'], '2');
        $this->assertSame($result[1]['name'], 'Peter');

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     * @depends testSelectArray
     */
    public function testDelete()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter")
            ;'
        );

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->delete('test', 'id=3');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->delete('test', 'id=2');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 1);

        $result = $this->db->selectArray('test', ['postfix' => 'ORDER BY id ASC']);
        $this->assertIsArray($result);

        $this->assertSame($result[0]['id'], '1');
        $this->assertSame($result[0]['name'], 'John');

        $this->db->query('DROP TABLE test');
    }

    /**
     * @depends testQuery
     * @depends testSelectArray
     */
    public function testIncorrectDelete()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `test` (
            `id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->db->query('INSERT INTO `test` (id, name) VALUES
            (1, "John"),
            (2, "Peter")
            ;'
        );

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);

        $result = $this->db->delete('test2', 'id=3');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);
        $this->assertFalse($result);

        $result = $this->db->delete('test', 'id2=2');

        $count = intval($this->db->query('SELECT COUNT(*) FROM test')->fetch_row()[0]);
        $this->assertSame($count, 2);
        $this->assertFalse($result);

        $this->db->query('DROP TABLE test');
    }
}