<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Sqlng\Pdo;

use Horde\Share\Test\Unnamespaced\Sqlng\BaseTestCase;
use Horde_Db_Adapter_Pdo_Mysql;
use PDO;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class MysqlTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo')
            || !in_array('mysql', PDO::getAvailableDrivers())) {
            self::$reason = 'No mysql extension or no mysql PDO driver';
            return;
        }
        $config = self::getConfig(
            'SHARE_SQL_PDO_MYSQL_TEST_CONFIG',
            dirname(__FILE__) . '/../..'
        );
        if ($config && !empty($config['share']['sql']['pdo_mysql'])) {
            self::$db = new Horde_Db_Adapter_Pdo_Mysql($config['share']['sql']['pdo_mysql']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No pdo_mysql configuration';
        }
    }
}
