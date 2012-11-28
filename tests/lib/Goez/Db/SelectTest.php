<?php

class Goez_Db_SelectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $options = array(
            'dbname' => 'goez_test',
            'host' => '127.0.0.1',
            'username' => 'www',
            'password' => '123456',
        );
        $this->_db = \Goez\Db::factory('mysql', $options);
        $this->_insertName = 'insert' . time();
    }

    public function tearDown()
    {
        $this->_db->closeConnection();
    }

    public function testSelect()
    {
        $sql = $this->_db->select()
                       ->from('users');
        $this->assertEquals('SELECT * FROM `users`', (string) $sql);

        $sql = $this->_db->select('name')
                          ->from('users');
        $this->assertEquals('SELECT `name` FROM `users`', (string) $sql);

        $sql = $this->_db->select(array('name', 'birthYear'))
                       ->from('users');
        $this->assertEquals('SELECT `name`, `birthYear` FROM `users`', (string) $sql);

        $sql = $this->_db->select(array('name' => 'userName', 'birthYear' => 'userBirthYear'))
                       ->from('users');
        $this->assertEquals('SELECT name AS `userName`, birthYear AS `userBirthYear` FROM `users`', (string) $sql);

        $sql = $this->_db->select()
                       ->distinct()
                       ->from('users');
        $this->assertEquals('SELECT DISTINCT * FROM `users`', (string) $sql);

        $sql = $this->_db->select(array('name', 'birthYear'))
                       ->distinct()
                       ->from('users');
        $this->assertEquals('SELECT DISTINCT `name`, `birthYear` FROM `users`', (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->where('birthYear = ?', 20);
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') AND (birthYear = 20)", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('id IN (?)', array(1, 2, 3));
        $this->assertEquals("SELECT * FROM `users` WHERE (id IN (1, 2, 3))", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name IN (?)', array('John', 'David', 'Bob'));
        $this->assertEquals("SELECT * FROM `users` WHERE (name IN ('John', 'David', 'Bob'))", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->where('birthYear = ?', 20)
                       ->group('name');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') AND (birthYear = 20) GROUP BY `name`", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->orWhere('birthYear = ?', 20)
                       ->group('name');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') OR (birthYear = 20) GROUP BY `name`", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('birthYear');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `birthYear` ASC", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('birthYear', 'DESC');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `birthYear` DESC", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->having('MAX(birthYear) < ?', 1980)
                       ->group('name')
                       ->order('birthYear', 'DESC');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` HAVING (MAX(birthYear) < 1980) ORDER BY `birthYear` DESC", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->having('MIN(birthYear) < ?', 1980)
                       ->having('? < MAX(birthYear)', 1980)
                       ->group('name')
                       ->order('birthYear', 'DESC');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` HAVING (MIN(birthYear) < 1980) AND (1980 < MAX(birthYear)) ORDER BY `birthYear` DESC", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->having('MIN(birthYear) < ?', 1980)
                       ->orHaving('? < MAX(birthYear)', 1980)
                       ->group('name')
                       ->order('birthYear', 'DESC');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` HAVING (MIN(birthYear) < 1980) OR (1980 < MAX(birthYear)) ORDER BY `birthYear` DESC", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('birthYear', 'DESC')
                       ->limit(3);
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `birthYear` DESC LIMIT 3", (string) $sql);

        $sql = $this->_db->select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('birthYear', 'DESC')
                       ->limit(3, 2);
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `birthYear` DESC LIMIT 2, 3", (string) $sql);
    }
}