<?php

declare(strict_types=1);

namespace Horde\Share\Test\Integration\Sqlng\Pdo;

use Horde\Share\Test\Unnamespaced\Sqlng\BaseTestCase;
use Horde_Db_Adapter_Pdo_Sqlite;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class SqliteTest extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::$db = new Horde_Db_Adapter_Pdo_Sqlite(['dbname' => ':memory:']);
        parent::setUpBeforeClass();
    }
}
