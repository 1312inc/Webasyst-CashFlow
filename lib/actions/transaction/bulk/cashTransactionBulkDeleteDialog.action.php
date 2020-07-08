<?php

/**
 * Class cashTransactionBulkDeleteDialogAction
 */
class cashTransactionBulkDeleteDialogAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $ids = waRequest::post('transaction_ids', '', waRequest::TYPE_STRING_TRIM);
        if (!$ids) {
            return;
        }

        $ids = json_decode($ids, true);
        $ids = array_filter($ids);
        if (!is_array($ids)) {
            return;
        }

        $this->view->assign(
            [
                'transactionCount' => count($ids),
                'transactionIds' => implode(',',$ids),
            ]
        );
    }
}
