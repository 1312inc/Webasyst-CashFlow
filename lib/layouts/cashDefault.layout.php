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
        if (!cash()->getUser()->hasAccessToApp()) {
            throw new kmwaForbiddenException('No app access');
        }

        $token = (new cashApiToken())->retrieveToken(cash()->getUser()->getContact());
        $this->executeAction('sidebar', new cashBackendSidebarAction());
        $showReviewWidget = cash()->getModel(cashTransaction::class)->select('count(id)')->limit(30)->fetchField() == 30;

        $this->view->assign(
            [
                'token' => $token,
                'content' => '<i class="icon16 loading"></i>',
                'isAdmin' => (int) cash()->getUser()->canImport(),
                'contextUser' => cash()->getUser(),
                'userId' => (int) wa()->getUser()->getId(),
                'show_review_widget' => $showReviewWidget,
            ]
        );
    }
}
