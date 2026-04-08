<?php

function migrate_sql($db)
{
    $migration = new Horde_Db_Migration_Base($db);

    /* Cleanup potential left-overs. */
    try {
        $migration->dropTable('test_shares');
        $migration->dropTable('test_shares_groups');
        $migration->dropTable('test_shares_users');
    } catch (Horde_Db_Exception $e) {
    }

    $t = $migration->createTable('test_shares', ['autoincrementKey' => 'share_id']);
    $t->column('share_name', 'string', ['limit' => 255, 'null' => false]);
    $t->column('share_owner', 'string', ['limit' => 255]);
    $t->column('share_parents', 'string', ['limit' => 4000]);
    $t->column('share_flags', 'integer', ['default' => 0, 'null' => false]);
    $t->column('perm_creator', 'integer', ['default' => 0, 'null' => false]);
    $t->column('perm_default', 'integer', ['default' => 0, 'null' => false]);
    $t->column('perm_guest', 'integer', ['default' => 0, 'null' => false]);
    $t->column('attribute_name', 'string', ['limit' => 255]);
    $t->column('attribute_desc', 'string', ['limit' => 255]);
    $t->column('attribute_clob', 'text');
    $t->end();

    $migration->addIndex('test_shares', ['share_name']);
    $migration->addIndex('test_shares', ['share_owner']);
    $migration->addIndex('test_shares', ['perm_creator']);
    $migration->addIndex('test_shares', ['perm_default']);
    $migration->addIndex('test_shares', ['perm_guest']);

    $t = $migration->createTable('test_shares_groups');
    $t->column('share_id', 'integer', ['null' => false]);
    $t->column('group_uid', 'string', ['limit' => 255, 'null' => false]);
    $t->column('perm', 'integer', ['null' => false]);
    $t->end();

    $migration->addIndex('test_shares_groups', ['share_id']);
    $migration->addIndex('test_shares_groups', ['group_uid']);
    $migration->addIndex('test_shares_groups', ['perm']);

    $t = $migration->createTable('test_shares_users');
    $t->column('share_id', 'integer', ['null' => false]);
    $t->column('user_uid', 'string', ['limit' => 255]);
    $t->column('perm', 'integer', ['null' => false]);
    $t->end();

    $migration->addIndex('test_shares_users', ['share_id']);
    $migration->addIndex('test_shares_users', ['user_uid']);
    $migration->addIndex('test_shares_users', ['perm']);

    $migration->migrate('up');
}
