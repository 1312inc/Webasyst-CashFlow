<?php

class cashSettingsAction extends cashViewAction
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
        $has_password = (new waAppSettingsModel())->get('cash', 'access_code', false);

        $this->view->assign([
            'has_password' => !!$has_password
        ]);
    }
}
