<?php

class cashBalanceflowWidget extends cashAbstractWidget
{
    /**
     * @var array<cashCurrencyVO>
     */
    private static $currencies;

    public static function getCurrencyFilterControl($name, $params): string
    {
        $templatePath = sprintf('%s/templates/CurrencyControl.html', dirname(__FILE__, 2));

        $currencies = self::getCurrencies();
        $widgetId = waRequest::get('id', true, waRequest::TYPE_INT);

        return self::renderTemplate($templatePath, [
            'name' => $name,
            'params' => $params,
            'widget' => wa()->getWidget($widgetId)->getInfo(),
            'currencies' => $currencies,
            'current_currency' => $params['value'] ?: self::getSettingsCurrencyCode($widgetId),
        ]);
    }

    public function defaultAction(): void
    {
        $this->incognitoUser();

        $token = (new cashApiToken())->retrieveToken(cash()->getUser()->getContact());

        $this->display([
            'token' => $token,
            'info' => $this->getInfo(),
            'currency_code' => self::getSettingsCurrencyCode($this->id),
            'currencies' => self::getCurrencies(),
            'locale' => wa()->getUser()->getLocale(),
            'url_root_absolute' => wa()->getRootUrl(true),
            'url_root' => wa()->getRootUrl(),
            'webasyst_ui' => wa()->whichUI('webasyst'),
        ]);

        $this->incognitoLogout();
    }

    /**
     * @return array<cashCurrencyVO>
     */
    private static function getCurrencies(): array
    {
        if (self::$currencies === null) {
            self::$currencies = array_reduce(
                cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact(),
                static function (array $carry, cashAccount $account) {
                    if (isset($carry[$account->getCurrency()])) {
                        return $carry;
                    }

                    $carry[$account->getCurrency()] = cashCurrencyVO::fromWaCurrency($account->getCurrency());

                    return $carry;
                },
                []
            );
        }

        return self::$currencies;
    }

    private static function getSettingsCurrencyCode($widgetId): string
    {
        $currencies = self::getCurrencies();
        $settings = self::getSettingModel()->get($widgetId);
        $firstCurrency = reset($currencies);

        return $settings['currency'] ?? ($firstCurrency ? $firstCurrency->getCode() : '');
    }
}
