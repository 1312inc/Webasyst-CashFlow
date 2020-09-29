<?php

$m = new waModel();

try {
    $m->exec('select slug from cash_category');
    $m->exec('alter table cash_category drop column slug');
} catch (Exception $ex) {
}
