<?php

$appPath = wa()->getAppPath('lib/class', 'cash');
$newPath = sprintf('%s/External', $appPath);
if (!file_exists($newPath)) {
    waFiles::create($newPath);
    waFiles::move(sprintf('%s/Shop', $appPath), sprintf('%s/Shop', $newPath));
}
