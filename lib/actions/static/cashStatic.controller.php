<?php

/**
 * Class cashStaticController
 */
class cashStaticController extends cashViewController
{
    public function execute()
    {
        $this->setLayout(new cashStaticLayout());
    }
}
