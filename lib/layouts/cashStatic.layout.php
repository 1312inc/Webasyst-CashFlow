<?php

/**
 * Class cashStaticLayout
 */
class cashStaticLayout extends waLayout
{
    public function execute()
    {
        $this->view->assign(
            [
                'token' => (new cashApiToken())->retrieveToken(cash()->getUser()->getContact()),
                'api_settings' => (new cashApiSystemGetSettingsHandler())->handle(null),
            ]
        );
    }
}
