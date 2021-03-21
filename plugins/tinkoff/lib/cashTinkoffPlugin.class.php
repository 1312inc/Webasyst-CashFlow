<?php

class cashTinkoffPlugin extends waPlugin
{
    public static function log($msg): void
    {
        if ($msg instanceof Exception) {
            $msg = sprintf("%s\n%s", $msg->getMessage(), $msg->getTraceAsString());
        }

        waLog::log($msg, 'cash/plugins/tinkoff.log');
    }

    public static function debug($msg): void
    {
        if (!waSystemConfig::isDebug()) {
            return;
        }

        if (!is_scalar($msg)) {
            $msg = wa_dump_helper($msg);
        }

        waLog::log($msg, 'cash/plugins/tinkoff.log');
        if (wa()->getEnv() === 'cli') {
            echo sprintf("%s\t%s\n", date('Y-m-d H:i:s.u'), $msg);
        }
    }
}
