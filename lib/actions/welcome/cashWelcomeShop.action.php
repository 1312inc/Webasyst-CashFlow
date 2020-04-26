<?php

/**
 * Class cashWelcomeShopAction
 */
class cashWelcomeShopAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function runAction($params = null)
    {
        if (waRequest::getMethod() === 'post') {
        }

        $this->view->assign(
            [
            ]
        );
    }
}
