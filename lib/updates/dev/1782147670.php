<?php

$model = new waModel();

try {
    $model->exec("ALTER TABLE cash_automation_log ADD automation_event varchar(255) NULL AFTER plugin_id");
} catch (waException $e) {
}
