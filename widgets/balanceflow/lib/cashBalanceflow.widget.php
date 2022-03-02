<?php

class cashBalanceflowWidget extends waWidget
{
    /**
     * @var array<cashCurrencyVO>
     */
    private static $currencies;

    /**
     * @var waWidgetSettingsModel
     */
    private static $settingsModel;

    public static function getCurrencyFilterControl($name, $params)
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

    public function defaultAction()
    {
        $token = (new cashApiToken())->retrieveToken(cash()->getUser()->getContact());

        $this->display([
            'token' => $token,
            'info' => $this->getInfo(),
            'currency_code' => self::getSettingsCurrencyCode($this->id),
        ]);
    }

    protected static function renderTemplate($template, $assign = []): string
    {
        if (!file_exists($template)) {
            return '';
        }
        $assign['ui'] = wa()->whichUI(wa()->getConfig()->getApplication());

        $view = wa()->getView();
        $old_vars = $view->getVars();
        $view->clearAllAssign();
        $view->assign($assign);
        $html = $view->fetch($template);
        $view->clearAllAssign();
        $view->assign($old_vars);

        return $html;
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

        return $settings['currency'] ?: ($firstCurrency ? $firstCurrency->getCode() : '');
    }

    private static function getSettingModel(): waWidgetSettingsModel
    {
        if (self::$settingsModel === null) {
            self::$settingsModel = new waWidgetSettingsModel();
        }

        return self::$settingsModel;
    }
}
