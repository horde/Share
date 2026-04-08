<?php

declare(strict_types=1);

/**
 * Copyright 2010-2026 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */

namespace Horde\Share\Test\Integration\Sql\Pdo;

use Horde\Share\Test\Unnamespaced\Sql\BaseTestCase;
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
