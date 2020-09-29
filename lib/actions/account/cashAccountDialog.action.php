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
        $id = waRequest::get('account_id', 0, waRequest::TYPE_INT);
        if (empty($id)) {
            if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
                throw new kmwaForbiddenException(_w('You are not allowed to create new accounts'));
            }

            $account = cash()->getEntityFactory(cashAccount::class)->createNew();
        } else {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($id);
            kmwaAssert::instance($account, cashAccount::class);

            if (!cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $account)) {
                throw new kmwaForbiddenException(_w('You are not allowed to access this account'));
            }
        }

        $currencies = [];
        foreach (waCurrency::getAll('all') as $currency) {
            $currencies[$currency['code']] = cashCurrencyVO::fromWaCurrency($currency['code']);
        }

        uasort($currencies, static function (cashCurrencyVO $a, cashCurrencyVO $b) {
            return $a->getCode() > $b->getCode();
        });

        switch (wa()->getUser()->getLocale()) {
            case 'ru_RU':
                $selectedCurrency = 'RUB';
                break;

            case 'uk_UA':
            case 'ua_UA':
                $selectedCurrency = 'UAH';
                break;

            default:
                $selectedCurrency = 'USD';
                break;
        }

        if (!$account->getId()) {
            $account->setCurrency($selectedCurrency);
        }

        $this->view->assign(
            [
                'account' => cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account),
                'currencies' => $currencies,
            ]
        );
    }
}
