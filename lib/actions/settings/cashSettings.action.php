<?php

/**
 * Class cashSettingsAction
 */
class cashSettingsAction extends waViewAction
{
    public function execute()
    {
        $message = 'Hello settings!';
        $this->view->assign('message', $message);
    }
}
