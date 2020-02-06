<?php

/**
 * Class cashAccountSortController
 */
class cashAccountSortController extends cashJsonController
{
    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function preExecute()
    {
        if (!cash()->getRightConfig()->isAdmin()) {
            throw new kmwaForbiddenException();
        }
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $order = waRequest::post('data', [], waRequest::TYPE_ARRAY);
        $saver = new cashAccountSaver();
        if (!$saver->sort($order)) {
            $this->errors[] = $saver->getError();
        }
    }
}
