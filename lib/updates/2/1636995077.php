<?php

$appPath = wa()->getAppPath(null, 'cash');
$path = [
    'templates/actions-legacy',
    'templates/layouts-legacy',
    'css/legacy',
];

foreach ($path as $item) {
    try {
        $filePath = sprintf('%s/%s', $appPath, $item);
        waFiles::delete($filePath, true);
    } catch (Exception $ex) {
        waLog::log('Error on deleting file ' . $filePath, 'cash/update.log');
    }
}
