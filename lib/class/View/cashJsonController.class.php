<?php

/**
 * Class cashJsonController
 */
abstract class cashJsonController extends kmwaWaJsonController
{
    protected function preExecute()
    {
        if (cashHelper::checkAccessCode() === false) {
            throw new waException(_w('Unauthorized'), 401);
        }

        parent::preExecute();
    }
}
