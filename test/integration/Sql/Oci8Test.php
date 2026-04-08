<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Sql;

use Horde\Share\Test\Unnamespaced\Sql\BaseTestCase;
use Horde_Db_Adapter_Oci8;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class Oci8Test extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('oci8')) {
            self::$reason = 'No oci8 extension';
            return;
        }
        $config = self::getConfig(
            'SHARE_SQL_OCI8_TEST_CONFIG',
            dirname(__FILE__) . '/..'
        );
        if ($config && !empty($config['share']['sql']['oci8'])) {
            self::$db = new Horde_Db_Adapter_Oci8($config['share']['sql']['oci8']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No oci8 configuration';
        }
    }
}
