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

namespace Horde\Share\Test\Unnamespaced\Sql;

use Horde\Share\Test\Unnamespaced\TestBase;
use Horde_Db_Migration_Base;
use Horde_Injector;
use Horde_Injector_TopLevel;
use Horde_Perms_Sql;
use Horde_Share_Object_Sql;
use Horde_Share_Sql;
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

    #[Depends('testAddShare')]
    public function testPermissions()
    {
        $this->permissions();
    }

    #[Depends('testAddShare')]
    public function testExists()
    {
        $this->exists();
    }

    #[Depends('testPermissions')]
    public function testCountShares()
    {
        $this->countShares();
    }

    #[Depends('testPermissions')]
    public function testGetShare()
    {
        $share = $this->getShare();
        $this->assertInstanceOf('Horde_Share_Object_Sql', $share);
    }

    #[Depends('testAddShare')]
    public function testHierarchy()
    {
        $this->hierarchy();
    }

    #[Depends('testGetShare')]
    public function testGetShareById()
    {
        $this->getShareById();
    }

    #[Depends('testGetShare')]
    public function testGetShares()
    {
        $this->getShares();
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
        $this->listAllShares();
    }

    #[Depends('testPermissions')]
    public function testListShares()
    {
        $this->listShares();
    }

    #[Depends('testPermissions')]
    public function testListSystemShares()
    {
        $this->listSystemShares();
    }

    #[Depends('testPermissions')]
    public function testGetPermission()
    {
        return $this->getPermission();
    }

    #[Depends('testPermissions')]
    public function testRemoveUserPermissions()
    {
        return $this->removeUserPermissions();
    }

    #[Depends('testRemoveUserPermissions')]
    public function testRemoveGroupPermissions()
    {
        $this->removeGroupPermissions();
    }

    #[Depends('testGetShare')]
    public function testRemoveShare()
    {
        $this->removeShare();
    }

    #[Depends('testGetShare')]
    public function testRenameShare()
    {
        $this->renameShare();
    }

    public function testCallback()
    {
        $this->callbackSetShareOb(new Horde_Share_Object_Sql([]));
    }

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../migration/sql.php';
        migrate_sql(self::$db);

        $group = new Horde_Share_Stub_Group();
        self::$share = new Horde_Share_Sql('test', 'john', new Horde_Perms_Sql(['db' => self::$db]), $group);
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
