<?php

/**
 * Class cashImportAction
 */
class cashImportAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     */
    public function runAction($params = null)
    {
        $message = 'Hello import!';
        $this->view->assign('message', $message);
    }
}
