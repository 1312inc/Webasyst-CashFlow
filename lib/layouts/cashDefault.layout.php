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
        $showReviewWidget = cash()->getModel(cashTransaction::class)
                ->select('count(id)')
                ->limit(500)
                ->fetchField() == 500;

        $this->view->assign(
            [
                'content' => '<i class="icon16 loading"></i>',
                'isAdmin' => (int) cash()->getUser()->isAdmin(),
                'contextUser' => cash()->getUser(),
                'userId' => (int) wa()->getUser()->getId(),
                'show_review_widget' => $showReviewWidget,
            ]
        );
    }
}
