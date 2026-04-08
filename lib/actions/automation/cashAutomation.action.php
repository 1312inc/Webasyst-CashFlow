<?php

/**
 * cashAutomationAction
 */
class cashAutomationAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        parent::preExecute();
    }

    public function runAction($params = null)
    {
        $this->view->assign([]);
    }
}
