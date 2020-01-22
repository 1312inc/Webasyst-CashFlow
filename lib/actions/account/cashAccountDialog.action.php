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
            $account = cash()->getEntityFactory(cashAccount::class)->createNew();
        } else {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($id);
            kmwaAssert::instance($account, cashAccount::class);
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

        $account->setCurrency($selectedCurrency);

        $this->view->assign(
            [
                'account' => cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account),
                'currencies' => $currencies,
            ]
        );
    }
}
