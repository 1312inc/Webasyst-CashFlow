<?php

/**
 * Class cashEventStorage
 */
final class cashEventStorage
{
    const ENTITY_INSERT_BEFORE = 'entity_insert.before';
    const ENTITY_INSERT_AFTER  = 'entity_insert.after';
    const ENTITY_DELETE_BEFORE = 'entity_delete.before';
    const ENTITY_DELETE_AFTER  = 'entity_delete.after';
    const ENTITY_UPDATE_BEFORE = 'entity_update.before';
    const ENTITY_UPDATE_AFTER  = 'entity_update.after';

    const WA_BACKEND_SIDEBAR              = 'backend_sidebar';
    const WA_BACKEND_TRANSACTION_DIALOG   = 'backend_transaction_dialog';
    const WA_BACKEND_IMPORT_FILE_UPLOADED = 'backend_import.file_uploaded';
    const WA_BACKEND_TRANSACTIONS_EXPORT  = 'backend_transactions_export';

    const SETTINGS_SAVE = 'settings_save';
    const TRANSACTION_PAGE_PREEXECUTE = 'transaction_page_preexecute';
    const ON_COUNT = 'on_count';
    const ACCOUNT_ARCHIVE = 'account_archive';
}
