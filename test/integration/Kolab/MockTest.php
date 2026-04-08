<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Kolab;

use Horde\Share\Test\Unnamespaced\TestBase;
use Horde_Cache;
use Horde_Cache_Storage_Mock;
use Horde_Injector;
use Horde_Injector_TopLevel;
use Horde_Kolab_Storage_Driver_Mock_Data;
use Horde_Kolab_Storage_Factory;
use Horde_Kolab_Storage_List_Tools;
use Horde_Log_Logger;
use Horde_Perms_Null;
use Horde_Share_Kolab;
use Horde_Share_Object_Sql;
use Horde_Share_Stub_Group;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Depends;

#[CoversNothing]
class MockTest extends TestBase
{
    private static $_data;

    private static $_shares = [];

    protected static $cache;

    public static function setUpBeforeClass(): void
    {
        if (!class_exists('Horde_Kolab_Storage_Driver_Mock_Data')) {
            return;
        }

        self::$_data = new Horde_Kolab_Storage_Driver_Mock_Data(
            [
                '' => ['permissions' => ['anyone' => 'alrid']],
                'user/john' => ['permissions' => ['anyone' => 'alrid']],
                'user/jane' => ['permissions' => ['anyone' => 'alrid']],
            ]
        );
        self::$cache = new Horde_Cache(new Horde_Cache_Storage_Mock());

        $group = new Horde_Share_Stub_Group();
        $GLOBALS['injector'] = new Horde_Injector(new Horde_Injector_TopLevel());
        $GLOBALS['injector']->setInstance('Horde_Group', $group);

        foreach (['john', 'jane', ''] as $user) {
            self::$_shares[$user] = new Horde_Share_Kolab(
                'mnemo',
                $user,
                new Horde_Perms_Null(),
                $group
            );
            $factory = new Horde_Kolab_Storage_Factory(
                [
                    'driver' => 'mock',
                    'params' => [
                        'data'   => self::$_data,
                        'username' => $user,
                    ],
                    'queries' => [
                        'list' => [
                            Horde_Kolab_Storage_List_Tools::QUERY_BASE => [
                                'cache' => true,
                            ],
                            Horde_Kolab_Storage_List_Tools::QUERY_ACL => [
                                'cache' => true,
                            ],
                            Horde_Kolab_Storage_List_Tools::QUERY_SHARE => [
                                'cache' => true,
                            ],
                        ],
                    ],
                    'cache'  => self::$cache,
                    'logger' => new Horde_Log_Logger(),
                ]
            );
            $storage = $factory->create();
            $factory->getDriver()->setGroups(
                ['john' => ['mygroup']]
            );
            self::$_shares[$user]->setStorage($storage);
        }
    }

    public function setUp(): void
    {
        if (!interface_exists('Horde_Kolab_Storage')) {
            $this->markTestSkipped('The Kolab_Storage package seems to be unavailable.');
        }
        self::$share = self::$_shares['john'];
        self::$share->getStorage()->getList()->getListSynchronization()->synchronize();
    }

    public function testGetApp()
    {
        $this->getApp('mnemo');
    }

    public function testAddShare()
    {
        $share = parent::addShare();
        $this->assertInstanceOf('Horde_Share_Object_Kolab', $share);
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
        $this->assertInstanceOf('Horde_Share_Object_Kolab', $share);
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
        $this->removeUserPermissions();
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

    public function testCallback()
    {
        $this->callbackSetShareOb(new Horde_Share_Object_Sql([]));
    }

    protected function switchAuth($user)
    {
        self::$share = self::$_shares[$user];
        self::$share->getStorage()->getList()->getListSynchronization()->synchronize();
    }

    protected function getCache()
    {
        return self::$cache;
    }
}
