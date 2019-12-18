<?php

class cashAccountAction extends waViewAction
{
    public function execute()
    {
        $message = 'Hello world!';
        $this->view->assign('message', $message);
    }
}
