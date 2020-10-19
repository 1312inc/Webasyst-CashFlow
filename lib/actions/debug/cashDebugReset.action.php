<?php

/**
 * Class cashDebugResetAction
 */
class cashDebugResetAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function runAction($params = null)
    {
        if (!cash()->getUser()->isRoot() && wa()->getEnv() !== 'cli') {
            throw new kmwaForbiddenException();
        }

        $model = new cashModel();

        $model->exec('drop table cash_transaction, cash_repeating_transaction, cash_import, cash_category, cash_account');
        $model->exec('delete from wa_app_settings where app_id = \'cash\'');

        cash()->clearCache();

        $this->view->assign('url', wa()->getAppUrl('cash'));
    }
}
