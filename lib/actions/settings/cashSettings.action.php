<?php

/**
 * Class cashSettingsAction
 */
class cashSettingsAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     */
    public function runAction($params = null)
    {
        $message = 'Hello settings!';
        $this->view->assign('message', $message);
    }
}
