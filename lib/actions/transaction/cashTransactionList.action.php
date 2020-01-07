<?php

/**
 * Class cashTransactionListAction
 */
class cashTransactionListAction extends cashViewAction
{
    /**
     * @throws waException
     */
    public function runAction($params = null)
    {
        $account = waRequest::request('account_id', 0, waRequest::TYPE_INT);
        $startDate = new DateTime(
            waRequest::request(
                'startDate',
                date('Y-m-d', strtotime('-90 days')),
                waRequest::TYPE_STRING_TRIM
            )
        );
        $endDate = new DateTime(
            waRequest::request(
                'endDate',
                date('Y-m-d', strtotime('+90 days')),
                waRequest::TYPE_STRING_TRIM
            )
        );

        if (empty($account)) {
            $accountIds = [];
        } else {
            $accountIds = [$account];
        }

        $dtoAssembler = new cashTransactionDtoAssembler();

        $today = new DateTime('today');
        $tomorrow = new DateTime('tomorrow');
        $upcoming = array_reverse($dtoAssembler->findByDatesAndAccount($tomorrow, $endDate, $accountIds), true);
        $completed = array_reverse($dtoAssembler->findByDatesAndAccount($startDate, $today, $accountIds), true);

        $this->view->assign(compact('upcoming', 'completed', 'account'));
    }
}
