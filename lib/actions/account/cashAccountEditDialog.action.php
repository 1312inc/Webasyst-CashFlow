<?php

/**
 * Class statusWalogDialogAction
 */
class cashAccountEditDialogAction extends statusViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaAssertException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $account = waRequest::post('account', [], waRequest::TYPE_ARRAY);
        if (empty($account['id'])) {
            $account = cash()->getEntityFactory(cashAccount::class)->createNewWithData($account);
        } else {
            $account = cash()->getEntityRepository(cashAccount::class)->findById((int)$account['id']);
            kmwaAssert::instance($account, cashAccount::class);
        }

        $this->view->assign(
            ['account' => cashAccountDto::createFromEntity($account)]
        );
    }
}
