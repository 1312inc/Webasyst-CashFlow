<?php

/**
 * Class cashAccountDialogAction
 */
class cashAccountDialogAction extends cashViewAction
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

        $currencies = [];
        foreach (waCurrency::getAll('all') as $currency) {
            $currencies[$currency['code']] = cashCurrencyVO::fromWaCurrency($currency['code']);
        }

        $this->view->assign(
            [
                'account' => cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account),
                'currencies' => $currencies,
            ]
        );
    }
}
