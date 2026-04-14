<?php

class cashTinkoffPluginResetController extends waJsonController
{
    public function execute()
    {
        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $rr = $plugin->saveSettings([
            'self_mode' => null,
            'profiles' => null,
            'tinkoff_token' => null
        ]);
    }
}
