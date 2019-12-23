<?php

class cashAccountAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws kmwaAssertException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $id = waRequest::get('id', waRequest::TYPE_INT, 0);

        if (!$id) {
            throw new kmwaNotFoundException(_w('Account not found'));
        }

        $account = cash()->getEntityRepository(cashAccount::class)->findById($id);
        kmwaAssert::instance($account, cashAccount::class);
    }
}
