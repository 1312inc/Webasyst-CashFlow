<?php

/**
 * Class cashDefaultLayout
 */
class cashDefaultLayout extends waLayout
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $this->executeAction('sidebar', new cashBackendSidebarAction());
        $this->view->assign(
            [
                'content' => '<i class="icon16 loading"></i>',
                'isAdmin' => (int)cash()->getRightConfig()->isAdmin(),
                'userId' => (int)wa()->getUser()->getId(),
            ]
        );
    }
}
