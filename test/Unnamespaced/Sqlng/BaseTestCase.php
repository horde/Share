<?php

declare(strict_types=1);

/**
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */

namespace Horde\Share\Test\Unnamespaced\Sqlng;

use Horde\Share\Test\Unnamespaced\TestBase;
use Horde_Db_Migration_Base;
use Horde_Injector;
use Horde_Injector_TopLevel;
use Horde_Perms_Sql;
use Horde_Share_Object_Sqlng;
use Horde_Share_Sqlng;
use Horde_Share_Stub_Group;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Depends;

#[CoversNothing]
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

    #[Depends('testAddShare')]
    public function testPermissions()
    {
        parent::permissions();
    }

    #[Depends('testAddShare')]
    public function testExists()
    {
        parent::exists();
    }

    #[Depends('testPermissions')]
    public function testCountShares()
    {
        parent::countShares();
    }

    #[Depends('testPermissions')]
    public function testGetShare()
    {
        $share = parent::getShare();
        $this->assertInstanceOf('Horde_Share_Object_Sqlng', $share);
    }

    #[Depends('testGetShare')]
    public function testGetShareById()
    {
        parent::getShareById();
    }

    #[Depends('testGetShare')]
    public function testGetShares()
    {
        parent::getShares();
    }

    public function testGetParent()
    {
        $share = self::$share->getShare('myshare');
        $child = self::$share->getShare('mychildshare');
        $this->assertEquals($share->getId(), $child->getParent()->getId());
    }

    #[Depends('testPermissions')]
    public function testListOwners()
    {
        $owners = self::$share->listOwners();
        $this->assertIsArray($owners);
        $this->assertTrue(in_array('john', $owners));
    }

    #[Depends('testPermissions')]
    public function testCountOwners()
    {
        $count = self::$share->countOwners();
        $this->assertTrue($count > 0);
    }

    #[Depends('testPermissions')]
    public function testListAllShares()
    {
        parent::listAllShares();
    }

    #[Depends('testPermissions')]
    public function testListShares()
    {
        parent::listShares();
    }

    #[Depends('testPermissions')]
    public function testListSystemShares()
    {
        parent::listSystemShares();
    }

    #[Depends('testPermissions')]
    public function testGetPermission()
    {
        return $this->getPermission();
    }

    #[Depends('testPermissions')]
    public function testRemoveUserPermissions()
    {
        return parent::removeUserPermissions();
    }

    #[Depends('testRemoveUserPermissions')]
    public function testRemoveGroupPermissions()
    {
        parent::removeGroupPermissions();
    }

    #[Depends('testGetShare')]
    public function testRemoveShare()
    {
        parent::removeShare();
    }

    #[Depends('testGetShare')]
    public function testRenameShare()
    {
        parent::renameShare();
    }

    public function testCallback()
    {
        $this->callbackSetShareOb(new Horde_Share_Object_Sqlng([]));
    }

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../migration/sqlng.php';
        migrate_sqlng(self::$db);

        $group = new Horde_Share_Stub_Group();
        self::$share = new Horde_Share_Sqlng('test', 'john', new Horde_Perms_Sql(['db' => self::$db]), $group);
        self::$share->setStorage(self::$db);

        // FIXME: Horde_Perms_Base::hasPermission() uses $GLOBALS['injector'] to look up Horde_Group
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
