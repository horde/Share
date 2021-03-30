<?php
/**
 * Prepare the test setup.
 */
namespace Horde\Share\Sql\Pdo;
use Horde\Share\Sql\BaseTestCase;
use \Horde_Test_Factory_Db;
use \Horde_Share_Stub_Group;
use \PDO;
use Horde\Share\Horde_Perms;
use \Horde_Support_Stub;
use \Horde_Db_Migration_Base;
use \Horde_Injector;

/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class SqliteTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        $factory_db = new Horde_Test_Factory_Db();

        if (class_exists(Horde_Injector::class)) {
            self::$db = $factory_db->create();
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'Horde_Injector not available';
        }
    }
}
