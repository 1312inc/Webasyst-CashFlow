<?php

/**
 * Class cashStaticLayout
 */
class cashStaticLayout extends waLayout
{
    public function execute()
    {
        $currencies = (new cashApiSystemGetCurrenciesHandler())->handle(null);

        $this->view->assign(
            [
                'token' => (new cashApiToken())->retrieveToken(cash()->getUser()->getContact()),
                'api_settings' => (new cashApiSystemGetSettingsHandler())->handle(null),
                'currencies' => json_encode($currencies, JSON_UNESCAPED_SLASHES || JSON_UNESCAPED_UNICODE),
            ]
        );
    }
}
