<?php

/**
 * cashTemplateAction
 */
class cashTemplateAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        parent::preExecute();
    }

    public function runAction($params = null)
    {
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
        $categories = cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact();
        $templates = (new cashTemplateModel())->getTemplates();

        $users = waUser::getUsers('cash');
        unset($users[wa()->getUser()->getId()]);
        $users[''] = _w('Все пользователи');

        $this->view->assign([
            'templates' => $templates,
            'accounts' => $accounts,
            'categories' => $categories,
            'users' => $users,
        ]);
    }
}
