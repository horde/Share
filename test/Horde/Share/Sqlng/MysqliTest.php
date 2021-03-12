<?php
/**
 * Prepare the test setup.
 */
namespace Horde\Share\Sqlng;
use Horde\Share\Sqlng\Base as Base;

require_once __DIR__ . '/Base.php';

/**
 * Copyright 2010-2017 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class MysqliTest extends Base
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('mysqli')) {
            self::$reason = 'No mysqli extension';
            return;
        }
        $config = self::getConfig('SHARE_SQL_MYSQLI_TEST_CONFIG',
                                  __DIR__ . '/..');
        if ($config && !empty($config['share']['sql']['mysqli'])) {
            self::$db = new Horde_Db_Adapter_Mysqli($config['share']['sql']['mysqli']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No mysqli configuration';
        }
    }
}
