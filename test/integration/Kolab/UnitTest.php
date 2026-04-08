<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Kolab;

use Horde_Cache;
use Horde_Cache_Storage_Mock;
use Horde_Exception_NotFound;
use Horde_Group_Mock;
use Horde_Kolab_Storage;
use Horde_Kolab_Storage_Factory;
use Horde_Kolab_Storage_Folder_Namespace;
use Horde_Kolab_Storage_List;
use Horde_Kolab_Storage_List_Query_List;
use Horde_Kolab_Storage_List_Tools;
use Horde_Log_Logger;
use Horde_Perms;
use Horde_Perms_Null;
use Horde_Share_Exception;
use Horde_Share_Kolab;
use Horde_Share_Object_Kolab;
use Horde_Share_Stub_Group;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class UnitTest extends TestCase
{
    private $storage;
    private $list;

    public function setUp(): void
    {
        if (!interface_exists('Horde_Kolab_Storage')) {
            $this->markTestSkipped('The Kolab_Storage package seems to be unavailable.');
        }
    }

    public function testGetStorage()
    {
        $storage = $this->createMock(Horde_Kolab_Storage::class);
        $list = $this->createMock(Horde_Kolab_Storage_List::class);
        $storage->expects($this->once())
            ->method('getList')
            ->willReturn($list);
        $driver = $this->_getDriver();
        $driver->setStorage($storage);
        $this->assertSame($list, $driver->getList());
    }

    public function testStorageMissing()
    {
        $this->expectException(Horde_Share_Exception::class);
        $driver = $this->_getDriver();
        $driver->getStorage();
    }

    public function testListArray()
    {
        $this->assertIsArray(
            $this->_getCompleteDriver()->listShares('john')
        );
    }

    public function testGetTypeString()
    {
        $driver = new Horde_Share_Kolab(
            'mnemo',
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
        $this->assertIsString($driver->getType());
    }

    public function testMnemoSupport()
    {
        $driver = new Horde_Share_Kolab(
            'mnemo',
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
        $this->assertEquals('note', $driver->getType());
    }

    public function testKronolithSupport()
    {
        $driver = new Horde_Share_Kolab(
            'kronolith',
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
        $this->assertEquals('event', $driver->getType());
    }

    public function testTurbaSupport()
    {
        $driver = new Horde_Share_Kolab(
            'turba',
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
        $this->assertEquals('contact', $driver->getType());
    }

    public function testNagSupport()
    {
        $driver = new Horde_Share_Kolab(
            'nag',
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
        $this->assertEquals('task', $driver->getType());
    }

    public function testSupportException()
    {
        $this->expectException(Horde_Share_Exception::class);
        $driver = new Horde_Share_Kolab(
            'NOTSUPPORTED',
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
    }

    public function testListIds()
    {
        $this->assertEquals(
            ['internal_id'],
            array_keys(
                $this->_getPrefilledDriver()->listShares('john')
            )
        );
    }

    public function testUndefinedId()
    {
        $this->expectException(Horde_Share_Exception::class);
        $object = new Horde_Share_Object_Kolab(null, new Horde_Group_Mock());
        $object->getId();
    }

    public function testUndefinedName()
    {
        $this->expectException(Horde_Share_Exception::class);
        $object = new Horde_Share_Object_Kolab(null, new Horde_Group_Mock());
        $object->getName();
    }

    public function testUndefinedPermissionId()
    {
        $object = new Horde_Share_Object_Kolab(null, new Horde_Group_Mock());
        $this->assertIsString(
            $object->getPermissionId()
        );
    }

    public function testIdFromName()
    {
        $share = $this->_getCompleteDriver();
        $object = $share->newShare('john', 'IGNORED', 'test');
        $this->assertEquals(
            ['john', 'test', 'INBOX'],
            $this->_decodeId($object->getId())
        );
    }

    public function testObjectId()
    {
        $object = new Horde_Share_Object_Kolab('test', new Horde_Group_Mock());
        $this->assertEquals('test', $object->getId());
    }

    public function testObjectName()
    {
        $object = new Horde_Share_Object_Kolab('test', new Horde_Group_Mock());
        $this->assertEquals('test', $object->getName());
    }

    public function testGetShare()
    {
        $result = $this->_decodeId(
            $this->_getPrefilledDriver()->getShare('internal_id')->getId()
        );
        $this->assertContains('john', $result);
        $this->assertContains('Calendar', $result);
    }

    public function testGetShareName()
    {
        $this->assertEquals(
            'internal_id',
            $this->_getPrefilledDriver()->getShare('internal_id')->getName()
        );
    }

    public function testExistsByName()
    {
        $this->assertTrue(
            $this->_getPrefilledDriver()->exists('internal_id')
        );
    }

    public function testDoesNotExists()
    {
        $this->assertFalse(
            $this->_getPrefilledDriver()->exists($this->_getId('john', 'DOES_NOT_EXIST'))
        );
    }

    public function testIdExists()
    {
        $this->assertTrue(
            $this->_getPrefilledDriver()->idExists($this->_getId('john', 'Calendar'))
        );
    }

    public function testIdDoesNotExists()
    {
        $this->assertFalse(
            $this->_getPrefilledDriver()->idExists($this->_getId('john', 'DOES_NOT_EXIST'))
        );
    }

    public function testGetShareById()
    {
        $this->assertEquals(
            ['john', 'Calendar'],
            $this->_decodeId(
                $this->_getPrefilledDriver()
                ->getShareById($this->_getId('john', 'Calendar'))
                ->getId()
            )
        );
    }

    public function testMissingShare()
    {
        $this->expectException(Horde_Exception_NotFound::class);
        $this->_getPrefilledDriver()->getShareById('DOES_NOT_EXIST');
    }

    public function testShareOwner()
    {
        $this->assertEquals(
            'john',
            $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'))
            ->get('owner')
        );
    }

    public function testShareName()
    {
        $this->assertEquals(
            'Calendar',
            $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'))
            ->get('name')
        );
    }

    public function testShareDescription()
    {
        $this->assertEquals(
            'DESCRIPTION',
            $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'))
            ->get('desc')
        );
    }

    public function testShareShareName()
    {
        $this->assertEquals(
            'internal_id',
            $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'))
            ->get('share_name')
        );
    }

    public function testShareFolder()
    {
        $this->assertEquals(
            'INBOX/Calendar',
            $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'))
            ->get('folder')
        );
    }

    public function testShareData()
    {
        $share = $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'));
        $share->set('other', 'OTHER');
        $share->save();
        $this->assertEquals(
            [
                'other' => 'OTHER',
                'share_name' => 'internal_id',
            ],
            $this->list
            ->getQuery(Horde_Kolab_Storage_List_Tools::QUERY_SHARE)
            ->getParameters('INBOX/Calendar')
        );
    }

    public function testSetDefault()
    {
        $share = $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'));
        $share->set('default', true);
        $share->save();
        $this->assertTrue(
            $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'))
            ->get('default')
        );
    }

    public function testNewShare()
    {
        $this->assertEquals(
            'john',
            $this->_getPrefilledDriver()
            ->newShare('john', 'IGNORE', 'test')
            ->get('owner')
        );
    }

    public function testNewShareSupportsName()
    {
        $this->assertEquals(
            'SHARE',
            $this->_getPrefilledDriver()
            ->newShare('john', 'SHARE', 'test')
            ->getName()
        );
    }

    public function testNewShareData()
    {
        $share = $this->_getPrefilledDriver()
            ->newShare('john', 'SHARE', 'test');
        $share->set('other', 'OTHER');
        $share->save();
        $result = $this->list
            ->getQuery(Horde_Kolab_Storage_List_Tools::QUERY_SHARE)
            ->getParameters('INBOX/test');
        $this->assertEquals(
            [
                'other' => 'OTHER',
                'share_name' => 'SHARE',
            ],
            $result
        );
    }

    public function testNewShareDelimiter()
    {
        $this->assertEquals(
            '/',
            $this->_getPrefilledDriver()
            ->newShare('john', 'IGNORE', 'test')
            ->get('delimiter')
        );
    }

    public function testNewShareSubpath()
    {
        $this->assertEquals(
            'test',
            $this->_getPrefilledDriver()
            ->newShare('john', 'IGNORE', 'test')
            ->get('subpath')
        );
    }

    public function testNewShareFolder()
    {
        $this->assertEquals(
            'INBOX/test',
            $this->_getPrefilledDriver()
            ->newShare('john', 'IGNORE', 'test')
            ->get('folder')
        );
    }

    public function testNewShareType()
    {
        $this->assertEquals(
            'event',
            $this->_getPrefilledDriver()
            ->newShare('john', 'IGNORE', 'test')
            ->get('type')
        );
    }

    public function testAddShare()
    {
        $share = $this->_getPrefilledDriver();
        $object = $share->newShare('john', 'IGNORED', 'Test');
        $share->addShare($object);
        $this->assertEquals(
            ['john', 'Test'],
            $this->_decodeId(
                $share->getShareById($this->_getId('john', 'Test'))->getId()
            )
        );
    }

    public function testShareAddedToList()
    {
        $share = $this->_getPrefilledDriver();
        $object = $share->newShare('john', 'SHARE_NAME', 'Test');
        $share->addShare($object);
        $this->assertContains(
            'SHARE_NAME',
            array_keys($share->listShares('john'))
        );
    }

    public function testDeleteShare()
    {
        $share = $this->_getPrefilledDriver();
        $object = $share->newShare('john', 'NAME', 'Test');
        $share->addShare($object);
        $share->removeShare($object);
        $this->assertNotContains(
            'NAME',
            array_keys($share->listShares('john'))
        );
    }

    public function testGetPermission()
    {
        $share = $this->_getPrefilledDriver();
        $object = $share->newShare('john', 'IGNORED', 'Test');
        $object->addUserPermission('tina', Horde_Perms::SHOW);
        $share->addShare($object);
        $this->assertEquals(
            ['tina' => Horde_Perms::SHOW],
            $share->getShareById($this->_getId('john', 'Test'))
                ->getPermission()->getUserPermissions()
        );
    }

    public function testSetPermission()
    {
        $share = $this->_getPrefilledDriver();
        $object = $share->newShare('john', 'IGNORED', 'Test');
        $object->addUserPermission('tina', Horde_Perms::SHOW);
        $share->addShare($object);
        $this->assertTrue(
            $share->getShareById($this->_getId('john', 'Test'))
                ->hasPermission('tina', Horde_Perms::SHOW)
        );
    }

    public function testOwnerPermission()
    {
        $share = $this->_getPrefilledDriver();
        $object = $share->newShare('john', 'IGNORED', 'Test');
        $share->addShare($object);
        $this->assertTrue(
            $share->getShareById($this->_getId('john', 'Test'))
                ->hasPermission('john', Horde_Perms::SHOW)
        );
    }

    public function testNewShareName()
    {
        $share = $this->_getCompleteDriver();
        $object = $share->newShare('john', 'NAME', 'test');
        $this->assertEquals('NAME', $object->get('share_name'));
    }

    public function testConstructFolderName()
    {
        $share = $this->_getCompleteDriver();
        $this->assertEquals('INBOX/test', $share->constructFolderName('john', 'test'));
    }

    public function testConstructFolderNameInComplexNamespace()
    {
        $this->expectException(Horde_Share_Exception::class);
        $share = $this->_getComplexNamespaceDriver();
        $this->assertEquals('INBOX/test', $share->constructFolderName('john', 'test'));
    }

    public function testConstructFolderNameInInbox()
    {
        $share = $this->_getComplexNamespaceDriver();
        $this->assertEquals('INBOX/test', $share->constructFolderName('john', 'test', 'INBOX'));
    }

    public function testConstructFolderNameInSecond()
    {
        $share = $this->_getComplexNamespaceDriver();
        $this->assertEquals('SECOND/test', $share->constructFolderName('john', 'test', 'SECOND'));
    }

    public function testSetDescription()
    {
        $share = $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'));
        $share->set('desc', 'NEW');
        $share->save();
        $this->assertEquals(
            'NEW',
            $this->list
            ->getQuery(Horde_Kolab_Storage_List_Tools::QUERY_SHARE)
            ->getDescription('INBOX/Calendar')
        );
    }

    public function testSetData()
    {
        $share = $this->_getPrefilledDriver()
            ->getShareById($this->_getId('john', 'Calendar'));
        $share->set('other', 'OTHER');
        $share->save();
        $result = $this->list
            ->getQuery(Horde_Kolab_Storage_List_Tools::QUERY_SHARE)
            ->getParameters('INBOX/Calendar');
        $this->assertEquals('OTHER', $result['other']);
        $this->assertEquals('internal_id', $result['share_name']);
    }

    public function testListShareCache()
    {
        $storage = $this->createMock(Horde_Kolab_Storage::class);
        $list = $this->getMockBuilder(Horde_Kolab_Storage_List_Tools::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->createMock(Horde_Kolab_Storage_List_Query_List::class);
        $query->expects($this->once())
            ->method('listByType')
            ->willReturn([]);
        $list->expects($this->exactly(3))
            ->method('getQuery')
            ->willReturn($query);
        $storage->expects($this->exactly(3))
            ->method('getList')
            ->willReturn($list);
        $driver = $this->_getDriver();
        $driver->setStorage($storage);
        $driver->listShares('test');
        $driver->listShares('test');
    }

    public function testEditableShares()
    {
        $this->assertEquals(
            1,
            count(
                $this->_getPermissionDriver()
                ->listShares('john', ['perm' => Horde_Perms::EDIT])
            )
        );
    }

    public function testRootLevel()
    {
        $this->assertEquals(
            3,
            count(
                $this->_getHierarchyDriver()
                ->listShares('john', ['all_levels' => false])
            )
        );
    }

    private function _getPrefilledDriver()
    {
        return $this->_getDriverWithData($this->_getPrefilledData());
    }

    private function _getCompleteDriver()
    {
        return $this->_getDriverWithData($this->_getCompleteData());
    }

    private function _getPermissionDriver()
    {
        return $this->_getDriverWithData($this->_getPermissionData());
    }

    private function _getHierarchyDriver()
    {
        return $this->_getDriverWithData($this->_getHierarchyData());
    }

    private function _getComplexNamespaceDriver()
    {
        return $this->_getDriverWithData($this->_getComplexNamespaceData());
    }

    private function _getDriverWithData($data)
    {
        $factory = new Horde_Kolab_Storage_Factory(
            [
                'driver' => 'mock',
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
                'params' => $data,
                'cache'  => new Horde_Cache(
                    new Horde_Cache_Storage_Mock()
                ),
                'logger' => new Horde_Log_Logger(),
            ]
        );
        $driver = $this->_getDriver('kronolith');
        $this->storage = $factory->create();
        $this->list = $this->storage->getList();
        $this->list->getListSynchronization()->synchronize();
        $driver->setStorage($this->storage);
        return $driver;
    }

    private function _getPrefilledData()
    {
        return [
            'username' => 'john',
            'data'   => $this->_getMockData(
                [
                    'user/john' => [],
                    'user/john/Calendar' => [
                        'a' => [
                            '/shared/vendor/kolab/folder-type' => 'event.default',
                            '/shared/comment' => 'DESCRIPTION',
                            '/shared/vendor/horde/share-params' => base64_encode(serialize(['share_name' => 'internal_id'])),
                        ],
                    ],
                ]
            ),
        ];
    }

    private function _getCompleteData()
    {
        return [
            'username' => 'john',
            'data'   => $this->_getMockData(
                [
                    'user/john' => null,
                ]
            ),
        ];
    }

    private function _getComplexNamespaceData()
    {
        return [
            'username' => 'john',
            'data'   => $this->_getMockData(
                [
                    'user/john' => null,
                ]
            ),
            'namespaces' => [
                [
                    'type' => Horde_Kolab_Storage_Folder_Namespace::PERSONAL,
                    'name' => 'INBOX/',
                    'delimiter' => '/',
                    'add' => true,
                ],
                [
                    'type' => Horde_Kolab_Storage_Folder_Namespace::PERSONAL,
                    'name' => 'SECOND/',
                    'delimiter' => '/',
                    'add' => true,
                ],
            ],
        ];
    }

    private function _getPermissionData()
    {
        return [
            'username' => 'john',
            'data'   => $this->_getMockData(
                [
                    'user/john' => [],
                    'user/john/Calendar' => [
                        't' => 'event.default',
                        'p' => ['john' => 'alrid'],
                    ],
                    'user/john/Listable' => [
                        't' => 'event',
                        'p' => ['john' => 'l'],
                    ],
                ]
            ),
        ];
    }

    private function _getHierarchyData()
    {
        return [
            'username' => 'john',
            'data'   => $this->_getMockData(
                [
                    'user/john' => [],
                    'user/john/Calendar' => ['t' => 'event.default'],
                    'user/john/Calendar/Private' => null,
                    'user/john/Calendar/Private/Family' => ['t' => 'event'],
                    'user/john/Calendar/Private/Family/Cooking' => ['t' => 'event'],
                    'user/john/Calendar/Private/Family/Party' => ['t' => 'event'],
                    'user/john/Work' => ['t' => 'event'],
                ]
            ),
        ];
    }

    private function _getDriver($app = 'mnemo')
    {
        return new Horde_Share_Kolab(
            $app,
            'john',
            new Horde_Perms_Null(),
            new Horde_Share_Stub_Group()
        );
    }

    private function _getMockData($elements)
    {
        $result = [];
        foreach ($elements as $path => $element) {
            if (!isset($element['p'])) {
                $folder = ['permissions' => ['anyone' => 'alrid']];
            } else {
                $folder = ['permissions' => $element['p']];
            }
            if (isset($element['a'])) {
                $folder['annotations'] = $element['a'];
            }
            if (isset($element['t'])) {
                $folder['annotations'] = [
                    '/shared/vendor/kolab/folder-type' => $element['t'],
                ];
            }
            $result[$path] = $folder;
        }
        return $result;
    }

    private function _getId($owner, $name)
    {
        return base64_encode(serialize([$owner, $name]));
    }

    private function _decodeId($id)
    {
        return unserialize(base64_decode($id));
    }
}
