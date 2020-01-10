<?php

/**
 * Class cashViewAction
 */
abstract class cashViewAction extends kmwaWaViewAction
{
    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        if (!cash()->getRightConfig()->hasAccessToApp()) {
            throw new kmwaForbiddenException(_w('No app access'));
        }
    }

    /**
     * @return array
     */
    protected function getDefaultViewVars()
    {
        return [
            'cash' => cash(),
            'isAdmin' => (int)wa()->getUser()->isAdmin(cashConfig::APP_ID),
            'serverTimezone' => date_default_timezone_get(),
        ];
    }
}
