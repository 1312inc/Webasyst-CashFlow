<?php

/**
 * Class cashTransactionGraphDataController
 */
class cashTransactionGraphDataController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        $accountId = waRequest::request('account_id', 0, waRequest::TYPE_INT);
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

        if (empty($accountId)) {
            $accountIds = [];
        } else {
            $accountIds = [$accountId];
        }

        $graphService = new cashGraphService();
        $graphData = new cashGraphColumnsDataDto($startDate, $endDate, $accountIds);
        $graphService->fillColumnCategoriesDataForAccounts($graphData);
        $graphService->fillBalanceDataForAccounts($graphData);

        $this->response = [
            'graphData' => $graphData,
        ];
    }
}
