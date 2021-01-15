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
        if (!cash()->getUser()->hasAccessToApp()) {
            throw new kmwaForbiddenException(_w('No app access'));
        }
    }

    /**
     * @return array
     * @throws waException
     */
    protected function getDefaultViewVars(): array
    {
        return [
            'cash' => cash(),
            'isAdmin' => (int) cash()->getUser()->canImport(),
            'contextUser' => cash()->getUser(),
            'serverTimezone' => date_default_timezone_get(),
            'waAppStaticUrl' => wa()->getAppStaticUrl(cashConfig::APP_ID),
        ];
    }
}
