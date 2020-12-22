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
        $token = (new cashApiToken())->retrieveToken(cash()->getUser()->getContact());
        $apiSettings = (new cashApiSystemGetSettingsHandler())->handle(null);

        $this->view->assign(
            [
                'token' => $token,
                'content' => '<i class="icon16 loading"></i>',
                'isAdmin' => (int) cash()->getUser()->canImport(),
                'contextUser' => cash()->getUser(),
                'userId' => (int) wa()->getUser()->getId(),
                'api_settings' => $apiSettings,
                'currencies' => json_encode($currencies, JSON_UNESCAPED_UNICODE),
                'categories' => json_encode($categories, JSON_UNESCAPED_UNICODE),
                'accounts' => json_encode($accounts, JSON_UNESCAPED_UNICODE),
            ]
        );
    }
}
