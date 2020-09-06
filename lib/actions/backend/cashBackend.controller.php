<?php

/**
 * Class cashBackendController
 */
class cashBackendController extends cashViewController
{
    public function execute()
    {
        $this->setLayout(new cashDefaultLayout());
    }
}
