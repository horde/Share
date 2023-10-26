<?php
/**
 * Prepare the test setup.
 */
namespace Horde\Share\Sqlng;
use Horde_Share_Test_Sqlng_Base as Base;

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
class Oci8Test extends Base
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('oci8')) {
            self::$reason = 'No oci8 extension';
            return;
        }
        $config = self::getConfig('SHARE_SQL_OCI8_TEST_CONFIG',
                                  __DIR__ . '/..');
        if ($config && !empty($config['share']['sql']['oci8'])) {
            self::$db = new Horde_Db_Adapter_Oci8($config['share']['sql']['oci8']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No oci8 configuration';
        }
    }
}
