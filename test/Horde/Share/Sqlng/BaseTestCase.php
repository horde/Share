<?php
/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
namespace Horde\Share\Sqlng;
use Horde\Share\TestBase as TestBase;
use \Horde_Share_Stub_Group;
use \Horde_Share_Sqlng;
use \Horde_Perms_Sql;
use \Horde_Injector;
use \Horde_Share_Object_Sqlng;
use \Horde_Db_Migration_Base;

class BaseTestCase extends TestBase
{
    protected static $db;

    protected static $reason;

    public function testGetApp()
    {
        $this->getApp('test');
    }

    public function testSetTable()
    {
        $this->assertEquals('test_sharesng', self::$share->getTable());
        self::$share->setTable('foo');
        $this->assertEquals('foo', self::$share->getTable());
        self::$share->setTable('test_shares');
    }

    public function testSetStorage()
    {
        self::$share->setStorage(self::$db);
        $this->assertEquals(self::$db, self::$share->getStorage());
    }

    public function testAddShare()
    {
        $share = parent::addShare();
        $this->assertInstanceOf('Horde_Share_Object_Sqlng', $share);
    }

    /**
     * @depends testAddShare
     */
    public function testPermissions()
    {
        parent::permissions();
    }

    /**
     * @depends testAddShare
     */
    public function testExists()
    {
        parent::exists();
    }

    /**
     * @depends testPermissions
     */
    public function testCountShares()
    {
        parent::countShares();
    }

    /**
     * @depends testPermissions
     */
    public function testGetShare()
    {
        $share = parent::getShare();
        $this->assertInstanceOf('Horde_Share_Object_Sqlng', $share);
    }

    /**
     * @depends testGetShare
     */
    public function testGetShareById()
    {
        parent::getShareById();
    }

    /**
     * @depends testGetShare
     */
    public function testGetShares()
    {
        parent::getShares();
    }

    /**
     */
    public function testGetParent()
    {
        $share = self::$share->getShare('myshare');
        $child = self::$share->getShare('mychildshare');
        $this->assertEquals($share->getId(), $child->getParent()->getId());
    }

    /**
     * @depends testPermissions
     */
     public function testListOwners()
     {
        $owners = self::$share->listOwners();
        $this->assertIsArray($owners);
        $this->assertTrue(in_array('john', $owners));
     }

    /**
     * @depends testPermissions
     */
     public function testCountOwners()
     {
        $count = self::$share->countOwners();
        $this->assertTrue($count > 0);
     }

    /**
     * @depends testPermissions
     */
    public function testListAllShares()
    {
        parent::listAllShares();
    }

    /**
     * @depends testPermissions
     */
    public function testListShares()
    {
        parent::listShares();
    }

    /**
     * @depends testPermissions
     */
    public function testListSystemShares()
    {
        parent::listSystemShares();
    }

    /**
     * @depends testPermissions
     */
    public function testGetPermission()
    {
        return $this->getPermission();
    }

    /**
     * @depends testPermissions
     */
    public function testRemoveUserPermissions()
    {
        return parent::removeUserPermissions();
    }

    /**
     * @depends testRemoveUserPermissions
     */
    public function testRemoveGroupPermissions()
    {
        parent::removeGroupPermissions();
    }

    /**
     * @depends testGetShare
     */
    public function testRemoveShare()
    {
        parent::removeShare();
    }

    /**
     * @depends testGetShare
     */
    public function testRenameShare()
    {
        parent::renameShare();
    }

    public function testCallback()
    {
        $this->callbackSetShareOb(new Horde_Share_Object_Sqlng(array()));
    }

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../migration/sqlng.php';
        migrate_sqlng(self::$db);

        $group = new Horde_Share_Stub_Group();
        self::$share = new Horde_Share_Sqlng('test', 'john', new Horde_Perms_Sql(array('db' => self::$db)), $group);
        self::$share->setStorage(self::$db);

        // FIXME
        //$GLOBALS['injector'] = new Horde_Injector(new Horde_Injector_TopLevel());
        //$GLOBALS['injector']->setInstance('Horde_Group', $group);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$db) {
            $migration = new Horde_Db_Migration_Base(self::$db);
            $migration->dropTable('test_shares');
            $migration->dropTable('test_shares_groups');
            $migration->dropTable('test_shares_users');
            self::$db->disconnect();
            self::$db = null;
        }
    }

    public function setUp(): void
    {
        if (!self::$db) {
            $this->markTestSkipped(self::$reason);
        }
    }
}
