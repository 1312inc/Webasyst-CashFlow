<?php

/**
 * Class cashViewController
 */
abstract class cashViewController extends waViewController
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

        parent::preExecute();
    }
}
