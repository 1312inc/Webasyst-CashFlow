<?php

/**
 * Class cashGraphService
 */
class cashGraphService
{
    const GROUP_BY_DAY   = 1;
    const GROUP_BY_MONTH = 2;

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     */
    public function fillColumnCategoriesDataForAccounts(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = [];
        switch ($this->determineGroup($graphData->startDate, $graphData->endDate)) {
            case self::GROUP_BY_DAY:
                $data = $model->getSummaryByDateBoundsAndAccountGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->accountIds
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getSummaryByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->accountIds
                );
                break;
        }

        foreach ($data as $date => $dateData) {
            foreach ($dateData as $dateDatum) {
                // grouping by currency
                $graphData->groups[$dateDatum['currency']] = ifset(
                    $graphData->groups[$dateDatum['currency']],
                    []
                );

                if (!in_array($dateDatum['hash'], $graphData->groups[$dateDatum['currency']])) {
                    $graphData->groups[$dateDatum['currency']][] = $dateDatum['hash'];
                }

                $graphData->columns[$dateDatum['hash']][$date] = (float)$dateDatum['summary'];
            }
        }
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     */
    public function fillBalanceDataForAccounts(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = [];
        switch ($this->determineGroup($graphData->startDate, $graphData->endDate)) {
            case self::GROUP_BY_DAY:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->accountIds
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->accountIds
                );
                break;
        }

        /** @var cashAccountModel $accountModel */
        $accountModel = cash()->getModel(cashAccount::class);
        $initialBalance = $accountModel->getStatDataForAccounts(
            '1970-01-01 00:00:00',
            $graphData->startDate->format('Y-m-d 23:59:59'),
            $graphData->accountIds
        );

        foreach ($graphData->dates as $date) {
            if (!isset($data[$date])) {
                continue;
            }

            foreach ($data[$date] as $datum) {
                $accountId = $datum['category_id'];
                if (!$graphData->accountIds) {
                    $accountId = 'All accounts';
                }
                $graphData->lines[$accountId][$date] += ((float)$datum['summary'] + (float)$initialBalance[$datum['category_id']]['summary']);
            }
        }
    }

//    private function generateDtos(array $data)
//    {
//        foreach ($data as $datum) {
//            if (!isset($graph[$datum['date']])) {
//                $graph[$datum['date']] = new cashGraphColumnDto($datum['date']);
//            }
//            if (!isset($graph[$datum['date']][$datum['currency']])) {
//                $graph[$datum['date']][$datum['currency']] = [];
//                new cashGraphCurrencyColumnDto($datum['currency']);
//
//            }
//            $graph[$datum['date']][$datum['currency']][] = [
//                'category' => $datum['category_id'],
//                'summary' => $datum['summary'],
//            ];
//
//            $accountColumn = new cashStatAccountDto($datum['account_id'], $datum['income'], $datum['expense'], $datum['summary']);
//        }
//    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     *
     * @return int
     */
    private function determineGroup(DateTime $startDate, DateTime $endDate)
    {
        if ($endDate->diff($startDate)->d > 90) {
            return self::GROUP_BY_MONTH;
        }

        return self::GROUP_BY_DAY;
    }
}