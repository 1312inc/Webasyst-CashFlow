<?php

/**
 * Class cashImportAction
 */
class cashImportAction extends waViewAction
{
    public function execute()
    {
        $message = 'Hello import!';
        $this->view->assign('message', $message);
    }
}
