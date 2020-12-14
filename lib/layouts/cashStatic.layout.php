<?php

/**
 * Class cashStaticLayout
 */
class cashStaticLayout extends waLayout
{
    public function execute()
    {
        $currencies = (new cashApiSystemGetCurrenciesHandler())->handle(null);
        $categories = (new cashApiCategoryGetListHandler())->handle(null);
        $accounts = (new cashApiAccountGetListHandler())->handle(null);

        $this->view->assign(
            [
                'token' => (new cashApiToken())->retrieveToken(cash()->getUser()->getContact()),
                'api_settings' => (new cashApiSystemGetSettingsHandler())->handle(null),
                'currencies' => json_encode($currencies, JSON_UNESCAPED_UNICODE),
                'categories' => json_encode($categories, JSON_UNESCAPED_UNICODE),
                'accounts' => json_encode($accounts, JSON_UNESCAPED_UNICODE),
            ]
        );
    }
}
