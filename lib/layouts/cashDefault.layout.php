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
        $this->executeAction('sidebar', new cashBackendSidebarAction());
        $this->executeAction('content', new cashAccountAction());
    }
}
