<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Sql\Pdo;

use Horde\Share\Test\Unnamespaced\Sql\BaseTestCase;
use Horde_Db_Adapter_Pdo_Pgsql;
use PDO;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class PgsqlTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('pdo')
            || !in_array('pgsql', PDO::getAvailableDrivers())) {
            self::$reason = 'No pgsql extension or no pgsql PDO driver';
            return;
        }
        $config = self::getConfig(
            'SHARE_SQL_PDO_PGSQL_TEST_CONFIG',
            dirname(__FILE__) . '/../..'
        );
        if ($config && !empty($config['share']['sql']['pdo_pgsql'])) {
            self::$db = new Horde_Db_Adapter_Pdo_Pgsql($config['share']['sql']['pdo_pgsql']);
            parent::setUpBeforeClass();
        } else {
            self::$reason = 'No pdo_pgsql configuration';
        }
    }
}
