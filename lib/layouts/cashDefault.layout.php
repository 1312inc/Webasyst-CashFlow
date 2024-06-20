<?php

/**
 * Class cashDefaultLayout
 */
class cashDefaultLayout extends waLayout
{
    /**
     * @throws waException
     */
    public function execute()
    {
        if (!cash()->getUser()->hasAccessToApp()) {
            throw new kmwaForbiddenException('No app access');
        }

        $token = (new cashApiToken())->retrieveToken(cash()->getUser()->getContact());
        $apiSettings = (new cashApiSystemGetSettingsHandler())->handle(null);
        $currencies = (new cashApiSystemGetCurrenciesHandler())->handle(null);
        $categories = (new cashApiCategoryGetListHandler())->handle(null);
        $accounts = (new cashApiAccountGetListHandler())->handle(null);

        $just_installed = wa('cash')->getSetting('just_installed');
        if ($just_installed) {
            (new waAppSettingsModel())->del('cash', 'just_installed');
        }

        /**
         * Include js after main app.js
         *
         * @event backend_layout
         * @return array[string]string $return[%plugin_id%][js]
         */
        $backendPlugins = wa()->event('backend_layout', $params);

        $this->view->assign(
            [
                'token' => $token,
                'content' => '<i class="icon16 loading"></i>',
                'isAdmin' => (int) cash()->getUser()->canImport(),
                'contextUser' => cash()->getUser(),
                'userId' => (int) wa()->getUser()->getId(),
                //'show_review_widget' => $showReviewWidget,
                'api_settings' => json_encode($apiSettings, JSON_UNESCAPED_SLASHES || JSON_UNESCAPED_UNICODE),
                'currencies' => json_encode($currencies, JSON_UNESCAPED_SLASHES || JSON_UNESCAPED_UNICODE),
                'categories' => json_encode($categories, JSON_UNESCAPED_SLASHES || JSON_UNESCAPED_UNICODE),
                'accounts' => json_encode($accounts, JSON_UNESCAPED_SLASHES || JSON_UNESCAPED_UNICODE),
                'backend_layout_plugins' => $backendPlugins,
                'emptyFlow' => $just_installed,
            ]
        );
    }
}
