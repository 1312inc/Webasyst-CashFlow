<?php
return array(
    'cash_account' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 64, 'null' => 0),
        'description' => array('text'),
        'icon' => array('varchar', 255),
        'currency' => array('varchar', 3, 'null' => 0),
        'current_balance' => array('decimal', "18,4", 'default' => '0.0000'),
        'customer_contact_id' => array('int', 11),
        'is_archived' => array('tinyint', 4, 'default' => '0'),
        'sort' => array('smallint', 6),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'cash_category' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 64, 'null' => 0),
        'type' => array('enum', "'income','expense','transfer'"),
        'color' => array('varchar', 7),
        'sort' => array('smallint', 6),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'cash_import' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'contact_id' => array('int', 11),
        'filename' => array('varchar', 255),
        'settings' => array('text'),
        'params' => array('text'),
        'success' => array('int', 11, 'default' => '0'),
        'fail' => array('int', 11, 'default' => '0'),
        'errors' => array('mediumtext'),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime'),
        'is_archived' => array('tinyint', 1, 'default' => '0'),
        'provider' => array('varchar', 50, 'default' => 'csv'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'cash_repeating_transaction' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'create_contact_id' => array('int', 11, 'null' => 0),
        'enabled' => array('smallint', 6, 'null' => 0, 'default' => '1'),
        'repeating_frequency' => array('int', 11, 'null' => 0, 'default' => '1'),
        'repeating_interval' => array('varchar', 20, 'null' => 0, 'default' => 'day'),
        'repeating_conditions' => array('text', 'null' => 0),
        'repeating_end_type' => array('varchar', 20, 'default' => 'never'),
        'repeating_end_conditions' => array('text'),
        'repeating_occurrences' => array('int', 11, 'default' => '0'),
        'date' => array('date', 'null' => 0),
        'account_id' => array('int', 11, 'null' => 0),
        'category_id' => array('int', 11),
        'amount' => array('decimal', "18,4", 'null' => 0),
        'description' => array('text'),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'cash_repeating_transaction_cash_account_id_fk' => 'account_id',
            'cash_repeating_transaction_cash_category_id_fk' => 'category_id',
        ),
    ),
    'cash_transaction' => array(
        'id' => array('bigint', 20, 'null' => 0, 'autoincrement' => 1),
        'date' => array('date', 'null' => 0),
        'datetime' => array('datetime', 'null' => 0),
        'account_id' => array('int', 11, 'null' => 0),
        'category_id' => array('int', 11),
        'amount' => array('decimal', "18,4", 'default' => '0.0000'),
        'description' => array('text'),
        'repeating_id' => array('int', 11),
        'create_contact_id' => array('int', 11, 'null' => 0),
        'create_datetime' => array('datetime', 'null' => 0),
        'update_datetime' => array('datetime'),
        'import_id' => array('varchar', 100),
        'is_archived' => array('tinyint', 1, 'default' => '0'),
        'external_hash' => array('varchar', 32),
        'external_source' => array('varchar', 20),
        ':keys' => array(
            'PRIMARY' => 'id',
            'cash_transaction_category_id_index' => 'category_id',
            'cash_transaction_datetime_create_contact_id_index' => array('datetime', 'create_contact_id'),
            'cash_transaction_repeating_id_index' => 'repeating_id',
            'cash_transaction_cash_account_id_fk' => 'account_id',
            'cash_transaction_is_archived_index' => 'is_archived',
            'cash_transaction_external_hash_index' => 'external_hash',
            'cash_transaction_external_source_index' => 'external_source',
        ),
    ),
);
