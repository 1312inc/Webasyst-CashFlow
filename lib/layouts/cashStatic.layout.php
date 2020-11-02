<?php

/**
 * Class cashStaticLayout
 */
class cashStaticLayout extends waLayout
{
    public function execute()
    {
        $token = (new cashApiToken())->retrieveToken(cash()->getUser()->getContact());

        $this->view->assign('token', $token);
    }
}
