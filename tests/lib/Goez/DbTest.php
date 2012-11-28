<?php

class Goez_DbTest extends PHPUnit_Framework_TestCase
{
    protected $_db = null;

    protected $_insertName = '';

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

    /**
     * @expectedException Exception
     */
    public function testFactoryFail()
    {
        $db = \Goez\Db::factory(null); // Exception
    }

    public function testFactoryAndVersion()
    {
        $this->assertFalse($this->_db->isConnected());
        $this->assertEquals('Goez\Db', get_class($this->_db));
        $this->assertRegExp('/^[\w\.]+$/', $this->_db->getServerVersion());
        $this->assertTrue($this->_db->isConnected());
    }

    public function testQuoteData()
    {
        $this->assertEquals('123', $this->_db->quote(123));
    }

    /**
     * @expectedException PDOException
     */
    public function testSetWrongFetchMode()
    {
        $this->_db->setFetchMode(null);
    }

    public function testInsertAndQuery()
    {
        $this->_db->setFetchMode(PDO::FETCH_OBJ);
        $name = $this->_insertName;
        $affected = $this->_db->insert('users', array(
            'name' => $name,
        ));
        $this->assertEquals(1, $affected);
        $id = $this->_db->lastInsertId('users');
        $sql = $this->_db->quoteInto('SELECT * FROM `users` WHERE `id` = ?', $id);
        $this->assertEquals("SELECT * FROM `users` WHERE `id` = '$id'", $sql);
        $user = $this->_db->fetchRow($sql);
        $this->assertEquals($name, $user->name);
    }

    public function testUpdateAndDeleteAndQuery()
    {
        $oldName = $this->_insertName;
        $newName = 'update' . time();
        $affected = $this->_db->update('users', array(
            'name' => $newName,
        ), array(
            'name = ?' => $oldName,
        ));
        $sql = $this->_db->quoteInto('SELECT * FROM `users` WHERE `name` = ?', $newName);
        $this->assertEquals("SELECT * FROM `users` WHERE `name` = '$newName'", $sql);
        $user = $this->_db->fetchRow($sql);
        $this->assertEquals($newName, $user['name']);
        $affected = $this->_db->delete('users', array(
            'name = ?' => $newName,
        ));
        $this->assertEquals(1, $affected);
    }

    public function testTrasactionCommit()
    {
        $this->_db->beginTransaction();
        $name = 'commit' . time();
        $affected = $this->_db->insert('products', array(
            'name' => $name,
        ));
        $id = $this->_db->lastInsertId('products');
        $this->assertEquals(1, $affected);
        $affected = $this->_db->delete('products', array(
            'id = ?' => $id,
        ));
        $this->assertEquals(1, $affected);
        $this->_db->commit();
    }

    public function testTrasactionRollback()
    {
        $this->_db->beginTransaction();
        $name = 'rollback' . time();
        $affected = $this->_db->insert('products', array(
            'name' => $name,
        ));
        $this->assertEquals(1, $affected);
        $this->_db->rollBack();
        $affected = $this->_db->delete('users', array(
            'name = ?' => $name,
        ));
        $this->assertEquals(0, $affected);
    }
}