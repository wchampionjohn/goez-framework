<?php
require_once dirname(dirname(__FILE__)) . '/GoEz/Sql.php';
require_once dirname(dirname(__FILE__)) . '/GoEz/Sql/Select.php';
require_once dirname(dirname(__FILE__)) . '/GoEz/Sql/Insert.php';
require_once dirname(dirname(__FILE__)) . '/GoEz/Sql/Update.php';
require_once dirname(dirname(__FILE__)) . '/GoEz/Sql/Delete.php';

class GoEz_SqlTest extends PHPUnit_Framework_TestCase
{
    public function testSelect()
    {
        $sql = GoEz_Sql::select()
                       ->from('users');
        $this->assertEquals('SELECT * FROM `users`', (string) $sql);

        $sql = GoEz_Sql::select('name')
                          ->from('users');
        $this->assertEquals('SELECT `name` FROM `users`', (string) $sql);

        $sql = GoEz_Sql::select(array('name', 'age'))
                       ->from('users');
        $this->assertEquals('SELECT `name`, `age` FROM `users`', (string) $sql);

        $sql = GoEz_Sql::select(array('name' => 'userName', 'age' => 'userAge'))
                       ->from('users');
        $this->assertEquals('SELECT name AS `userName`, age AS `userAge` FROM `users`', (string) $sql);

        $sql = GoEz_Sql::select()
                       ->distinct()
                       ->from('users');
        $this->assertEquals('SELECT DISTINCT * FROM `users`', (string) $sql);

        $sql = GoEz_Sql::select(array('name', 'age'))
                       ->distinct()
                       ->from('users');
        $this->assertEquals('SELECT DISTINCT `name`, `age` FROM `users`', (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->where('age = ?', 20);
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') AND (age = 20)", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('id IN (?)', array(1, 2, 3));
        $this->assertEquals("SELECT * FROM `users` WHERE (id IN (1, 2, 3))", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name IN (?)', array('John', 'David', 'Bob'));
        $this->assertEquals("SELECT * FROM `users` WHERE (name IN ('John', 'David', 'Bob'))", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->where('age = ?', 20)
                       ->group('name');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') AND (age = 20) GROUP BY `name`", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('age');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `age` ASC", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('age', 'DESC');
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `age` DESC", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('age', 'DESC')
                       ->limit(3);
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `age` DESC LIMIT 3", (string) $sql);

        $sql = GoEz_Sql::select()
                       ->from('users')
                       ->where('name = ?', 'John')
                       ->group('name')
                       ->order('age', 'DESC')
                       ->limit(3, 2);
        $this->assertEquals("SELECT * FROM `users` WHERE (name = 'John') GROUP BY `name` ORDER BY `age` DESC LIMIT 2, 3", (string) $sql);
    }

    public function testInsert()
    {
        $sql = GoEz_Sql::insert('users', array(
            'name' => 'John',
            'age' => 20,
        ));
        $this->assertEquals("INSERT INTO `users` (`name`, `age`) VALUES ('John', 20)", (string) $sql);
    }

    public function testUpdate()
    {
        $sql = GoEz_Sql::update('users', array(
            'name' => 'John',
            'age' => 21,
        ), array(
            'id = ?' => 1,
        ));
        $this->assertEquals("UPDATE `users` SET `name` = 'John', `age` = 21 WHERE (id = 1)", (string) $sql);

        $sql = GoEz_Sql::update('users', array(
            'name' => 'John',
            'age' => 21,
        ), array(
            'id = ?' => 1,
            'age > 0',
        ));
        $this->assertEquals("UPDATE `users` SET `name` = 'John', `age` = 21 WHERE (id = 1) AND (age > 0)", (string) $sql);
    }

    public function testDelete()
    {
        $sql = GoEz_Sql::delete('users', array(
            'id = ?' => 1,
        ));
        $this->assertEquals("DELETE FROM `users` WHERE (id = 1)", (string) $sql);

        $sql = GoEz_Sql::delete('users', array(
            'age > 20',
        ));
        $this->assertEquals("DELETE FROM `users` WHERE (age > 20)", (string) $sql);
    }
}