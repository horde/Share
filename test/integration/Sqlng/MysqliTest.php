<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Sqlng;

use Horde\Share\Test\Unnamespaced\Sqlng\BaseTestCase;
use Horde_Db_Adapter_Mysqli;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class MysqliTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('mysqli')) {
            self::$reason = 'No mysqli extension';
            return;
        }
        $config = self::getConfig(
            'SHARE_SQL_MYSQLI_TEST_CONFIG',
            dirname(__FILE__) . '/..'
        );
        if ($config && !empty($config['share']['sql']['mysqli'])) {
            self::$db = new Horde_Db_Adapter_Mysqli($config['share']['sql']['mysqli']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No mysqli configuration';
        }
    }
}
