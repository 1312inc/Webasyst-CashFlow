<?php

/**
 * Class cashWelcomeLayout
 */
class cashWelcomeLayout extends waLayout
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->executeAction('content',new cashWelcomeShopAction());
        $this->view->assign(
            [
                'isAdmin' => (int)cash()->getRightConfig()->isAdmin(),
                'userId' => (int)wa()->getUser()->getId(),
            ]
        );
    }
}
