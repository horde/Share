<?php
/**
 * Integration test for the Kolab driver based on the in-memory mock driver.
 *
 * PHP version 5
 *
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@pardus.de>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
namespace Horde\Share\Kolab;
use Horde\Share\TestBase as TestBase;

/**
 * Integration test for the Kolab driver based on the in-memory mock driver.
 *
 * Copyright 2011-2017 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @author     Gunnar Wrobel <wrobel@pardus.de>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class MockTest extends TestBase
{
    private static $_data;

    private static $_shares = array();

    protected static $cache;

    public static function setUpBeforeClass(): void
    {
        if (!class_exists('Horde_Kolab_Storage_Driver_Mock_Data')) {
            return;
        }

        self::$_data = new Horde_Kolab_Storage_Driver_Mock_Data(
            array(
                '' => array('permissions' => array('anyone' => 'alrid')),
                'user/john' => array('permissions' => array('anyone' => 'alrid')),
                'user/jane' => array('permissions' => array('anyone' => 'alrid')),
            )
        );
        self::$cache = new Horde_Cache(new Horde_Cache_Storage_Mock());

        $group = new Horde_Share_Stub_Group();
        // FIXME
        $GLOBALS['injector'] = new Horde_Injector(new Horde_Injector_TopLevel());
        $GLOBALS['injector']->setInstance('Horde_Group', $group);

        foreach (array('john', 'jane', '') as $user) {
            self::$_shares[$user] = new Horde_Share_Kolab(
                'mnemo', $user, new Horde_Perms_Null(), $group
            );
            $factory = new Horde_Kolab_Storage_Factory(
                array(
                    'driver' => 'mock',
                    'params' => array(
                        'data'   => self::$_data,
                        'username' => $user
                    ),
                    'queries' => array(
                        'list' => array(
                            Horde_Kolab_Storage_List_Tools::QUERY_BASE => array(
                                'cache' => true
                            ),
                            Horde_Kolab_Storage_List_Tools::QUERY_ACL => array(
                                'cache' => true
                            ),
                            Horde_Kolab_Storage_List_Tools::QUERY_SHARE => array(
                                'cache' => true
                            ),
                        )
                    ),
                    'cache'  => self::$cache,
                    'logger' => new Horde_Log_Logger()
                )
            );
            $storage = $factory->create();
            $factory->getDriver()->setGroups(
                array('john' => array('mygroup'))
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
        $this->assertInstanceOf('Horde_Share_Object_Kolab', $share);
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
        $this->removeUserPermissions();
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

    public function testCallback()
    {
        $this->callbackSetShareOb(new Horde_Share_Object_Sql(array()));
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

/**
 NOTES

 - Check extra API calls in SQL driver
 - add server test
*/
