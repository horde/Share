<?php

function migrate_sqlng($db)
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
    $t->column('perm_creator_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_creator_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_creator_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_creator_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_default_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_default_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_default_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_default_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_guest_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_guest_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_guest_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_guest_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
    $t->column('attribute_name', 'string', ['limit' => 255]);
    $t->column('attribute_desc', 'string', ['limit' => 255]);
    $t->column('attribute_clob', 'text');
    $t->end();

    $migration->addIndex('test_shares', ['share_name']);
    $migration->addIndex('test_shares', ['share_owner']);
    $migration->addIndex('test_shares', ['perm_creator_' . Horde_Perms::SHOW]);
    $migration->addIndex('test_shares', ['perm_creator_' . Horde_Perms::READ]);
    $migration->addIndex('test_shares', ['perm_creator_' . Horde_Perms::EDIT]);
    $migration->addIndex('test_shares', ['perm_creator_' . Horde_Perms::DELETE]);
    $migration->addIndex('test_shares', ['perm_default_' . Horde_Perms::SHOW]);
    $migration->addIndex('test_shares', ['perm_default_' . Horde_Perms::READ]);
    $migration->addIndex('test_shares', ['perm_default_' . Horde_Perms::EDIT]);
    $migration->addIndex('test_shares', ['perm_default_' . Horde_Perms::DELETE]);
    $migration->addIndex('test_shares', ['perm_guest_' . Horde_Perms::SHOW]);
    $migration->addIndex('test_shares', ['perm_guest_' . Horde_Perms::READ]);
    $migration->addIndex('test_shares', ['perm_guest_' . Horde_Perms::EDIT]);
    $migration->addIndex('test_shares', ['perm_guest_' . Horde_Perms::DELETE]);

    $t = $migration->createTable('test_shares_groups');
    $t->column('share_id', 'integer', ['null' => false]);
    $t->column('group_uid', 'string', ['limit' => 255, 'null' => false]);
    $t->column('perm_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
    $t->end();

    $migration->addIndex('test_shares_groups', ['share_id']);
    $migration->addIndex('test_shares_groups', ['group_uid']);
    $migration->addIndex('test_shares_groups', ['perm_' . Horde_Perms::SHOW]);
    $migration->addIndex('test_shares_groups', ['perm_' . Horde_Perms::READ]);
    $migration->addIndex('test_shares_groups', ['perm_' . Horde_Perms::EDIT]);
    $migration->addIndex('test_shares_groups', ['perm_' . Horde_Perms::DELETE]);

    $t = $migration->createTable('test_shares_users');
    $t->column('share_id', 'integer', ['null' => false]);
    $t->column('user_uid', 'string', ['limit' => 255]);
    $t->column('perm_' . Horde_Perms::SHOW, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_' . Horde_Perms::READ, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_' . Horde_Perms::EDIT, 'boolean', ['default' => false, 'null' => false]);
    $t->column('perm_' . Horde_Perms::DELETE, 'boolean', ['default' => false, 'null' => false]);
    $t->end();

    $migration->addIndex('test_shares_users', ['share_id']);
    $migration->addIndex('test_shares_users', ['user_uid']);
    $migration->addIndex('test_shares_users', ['perm_' . Horde_Perms::SHOW]);
    $migration->addIndex('test_shares_users', ['perm_' . Horde_Perms::READ]);
    $migration->addIndex('test_shares_users', ['perm_' . Horde_Perms::EDIT]);
    $migration->addIndex('test_shares_users', ['perm_' . Horde_Perms::DELETE]);

    $migration->migrate('up');
}
