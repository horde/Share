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
namespace Horde\Share\Sql;
use Horde\Share\TestBase as TestBase;
use \Horde_Share_Stub_Group;
use \Horde_Share_Sql;
use \Horde_Perms_Sql;
use \Horde_Injector;
use \Horde_Share_Object_Sql;
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
        $this->assertEquals('test_shares', self::$share->getTable());
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
        $share = $this->addShare();
        $this->assertInstanceOf('Horde_Share_Object_Sql', $share);
    }

    /**
     * @depends testAddShare
     */
    public function testPermissions()
    {
        $this->permissions();
    }

    /**
     * @depends testAddShare
     */
    public function testExists()
    {
        $this->exists();
    }

    /**
     * @depends testPermissions
     */
    public function testCountShares()
    {
        $this->countShares();
    }

    /**
     * @depends testPermissions
     */
    public function testGetShare()
    {
        $share = $this->getShare();
        $this->assertInstanceOf('Horde_Share_Object_Sql', $share);
    }

    /**
     * @depends testAddShare
     */
    public function testHierarchy()
    {
        $this->hierarchy();
    }

    /**
     * @depends testGetShare
     */
    public function testGetShareById()
    {
        $this->getShareById();
    }

    /**
     * @depends testGetShare
     */
    public function testGetShares()
    {
        $this->getShares();
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
        $this->listAllShares();
    }

    /**
     * @depends testPermissions
     */
    public function testListShares()
    {
        $this->listShares();
    }

    /**
     * @depends testPermissions
     */
    public function testListSystemShares()
    {
        $this->listSystemShares();
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
        return $this->removeUserPermissions();
    }

    /**
     * @depends testRemoveUserPermissions
     */
    public function testRemoveGroupPermissions()
    {
        $this->removeGroupPermissions();
    }

    /**
     * @depends testGetShare
     */
    public function testRemoveShare()
    {
        $this->removeShare();
    }

    /**
     * @depends testGetShare
     */
    public function testRenameShare()
    {
        $this->renameShare();
    }

    public function testCallback()
    {
        $this->callbackSetShareOb(new Horde_Share_Object_Sql(array()));
    }

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../migration/sql.php';
        migrate_sql(self::$db);

        $group = new Horde_Share_Stub_Group();
        self::$share = new Horde_Share_Sql('test', 'john', new Horde_Perms_Sql(array('db' => self::$db)), $group);
        self::$share->setStorage(self::$db);

        // FIXME
        $GLOBALS['injector'] = new Horde_Injector(new Horde_Injector_TopLevel());
        $GLOBALS['injector']->setInstance('Horde_Group', $group);
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
