<?php

/**
 * Class cashBackendController
 */
class cashBackendController extends waViewController
{
    public function execute()
    {
        $this->setLayout(new cashDefaultLayout());
    }
}
