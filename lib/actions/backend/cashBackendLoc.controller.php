<?php

/**
 * A list of localized strings to use in JS.
 */
class cashBackendLocController extends waViewController
{
    protected function preExecute()
    {
    }

    public function execute()
    {
        $this->executeAction(new cashBackendLocAction());
    }
}
