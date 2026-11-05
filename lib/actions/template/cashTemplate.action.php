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
        $my_name = $users[wa()->getUser()->getId()];
        unset($users[wa()->getUser()->getId()]);
        $users = [
            wa()->getUser()->getId() => $my_name.' ('._w('Я').')',
            '' => _w('Все пользователи'),
        ] + $users;

        $this->view->assign([
            'templates' => $templates,
            'accounts' => $accounts,
            'categories' => $categories,
            'users' => $users,
        ]);
    }
}
