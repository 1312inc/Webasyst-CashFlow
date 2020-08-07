<?php

/**
 * Class cashShopResetImportDialogAction
 */
class cashShopResetImportDialogAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws waException
     */
    public function runAction($params = null)
    {
        wa()->getStorage()->del('cash.shop_integration_reset_code');
        $code = 'YES ' . substr(md5(mt_rand()), 0, 3);
        wa()->getStorage()->set('cash.shop_integration_reset_code', $code);
        $this->view->assign('code', $code);
    }
}
