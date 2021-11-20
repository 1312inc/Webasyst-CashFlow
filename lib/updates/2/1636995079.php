<?php

$m = new cashModel();

try {
    $alter = false;
    $m->exec('SELECT parent_category_id FROM cash_category limit 1');
} catch (Exception $ex) {
    $alter = true;
}

if ($alter) {
    $m->startTransaction();

    try {
        $m->exec('ALTER TABLE cash_category ADD category_parent_id INT DEFAULT NULL NULL');
        $m->exec('ALTER TABLE cash_category ADD glyph VARCHAR(32) DEFAULT NULL NULL');
        $m->exec('CREATE INDEX cash_category_category_parent_id_index ON cash_category (category_parent_id)');
        $m->exec(
            'ALTER TABLE cash_category
                    ADD CONSTRAINT cash_category_category_parent_id_fk
                        FOREIGN KEY (category_parent_id) REFERENCES cash_category (id)
                            ON UPDATE CASCADE ON DELETE SET NULL'
        );
        $m->commit();
    } catch (Exception $ex) {
        $m->rollback();
        waLog::log($ex->getMessage(), 'cash/update.log');
        waLog::log($ex->getTraceAsString(), 'cash/update.log');
    }
}
