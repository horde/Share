<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Sqlng;

use Horde\Share\Test\Unnamespaced\Sqlng\BaseTestCase;
use Horde_Db_Adapter_Mysql;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class MysqlTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('mysql')) {
            self::$reason = 'No mysql extension';
            return;
        }
        $config = self::getConfig(
            'SHARE_SQL_MYSQL_TEST_CONFIG',
            dirname(__FILE__) . '/..'
        );
        if ($config && !empty($config['share']['sql']['mysql'])) {
            self::$db = new Horde_Db_Adapter_Mysql($config['share']['sql']['mysql']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No mysql configuration';
        }
    }
}
