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
        $cash_settings = (new waAppSettingsModel())->get('cash');

        $this->view->assign([
            'has_password'  => !!ifempty($cash_settings, 'access_code', false),
            'email'         => ifset($cash_settings, 'master_email', ''),
            'big_bang_date' => ifset($cash_settings, 'big_bang_date', '')
        ]);
    }
}
