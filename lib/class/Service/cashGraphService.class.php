<?php

/**
 * Class cashGraphService
 */
class cashGraphService
{
    const GROUP_BY_DAY   = 1;
    const GROUP_BY_MONTH = 2;

    /**
     * @return array
     */
    public static function getChartPeriods()
    {
        return [
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -30),
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -90),
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -180),
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -365),
            new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, -3),
            new cashGraphPeriodVO(cashGraphPeriodVO::ALL_TIME_PERIOD),
        ];
    }

    /**
     * @return array
     */
    public static function getForecastPeriods()
    {
        return [
            new cashGraphPeriodVO(cashGraphPeriodVO::NONE_PERIOD),
            new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, 1),
            new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, 3),
            new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, 6),
            new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, 12),
        ];
    }

    /**
     * @return cashGraphPeriodVO
     */
    public function getDefaultChartPeriod()
    {
        return new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -90);
    }

    /**
     * @return cashGraphPeriodVO
     */
    public function getDefaultForecastPeriod()
    {
        return new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, 1);
    }

    /**
     * @param DateTimeInterface $dateTime
     *
     * @return cashGraphPeriodVO
     * @throws Exception
     */
    public function getChartPeriodByDate(DateTimeInterface $dateTime)
    {
        $periods = array_reverse(self::getChartPeriods());
        $periods[] = new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 0);
        for ($i = 0, $iMax = count($periods) - 1; $i < $iMax; $i++) {
            if ($dateTime >= $periods[$i]->getDate() && $dateTime < $periods[$i + 1]->getDate()) {
                return $periods[$i + 1];
            }
        }

        return reset($periods);
    }

    /**
     * @param DateTimeInterface $dateTime
     *
     * @return cashGraphPeriodVO
     * @throws Exception
     */
    public function getForecastPeriodByDate(DateTimeInterface $dateTime)
    {
        $periods = self::getForecastPeriods();
        if ($periods[0]->getDate()->diff($dateTime)->d === 0) {
            return $periods[0];
        }

        for ($i = 1, $iMax = count($periods); $i < $iMax; $i++) {
            if ($dateTime < $periods[$i]->getDate()) {
                return $periods[$i];
            }
        }

        return end($periods);
    }

    /**
     * @param DateTime                     $startDate
     * @param DateTime                     $endDate
     * @param cashTransactionPageFilterDto $filterDto
     *
     * @return cashGraphColumnsDataDto
     * @throws waException
     */
    public function createDto(DateTime $startDate, DateTime $endDate, cashTransactionPageFilterDto $filterDto)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($filterDto->type) {
            case cashTransactionPageFilterDto::FILTER_ACCOUNT:
                $dateBounds = $model->getDateBoundsByAccounts(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $filterDto->id
                );
                break;
            case cashTransactionPageFilterDto::FILTER_CATEGORY:
                $dateBounds = $model->getDateBoundsByCategories(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $filterDto->id
                );
                break;
        }

        if (!empty($dateBounds['startDate'])) {
            $startDate = (new DateTime($dateBounds['startDate']))->modify('-1 day');
        }

        if (!empty($dateBounds['endDate'])) {
            $endDate = (new DateTime($dateBounds['endDate']))->modify('+1 day');
        }

        return new cashGraphColumnsDataDto($startDate, $endDate, $filterDto);
    }

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
                    $graphData->filterDto->id
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getSummaryByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->id
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

                $graphData->columns[$dateDatum['hash']][$date] = (float)abs($dateDatum['summary']);
            }
        }
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     */
    public function fillColumnCategoriesDataForCategories(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = [];
        switch ($this->determineGroup($graphData->startDate, $graphData->endDate)) {
            case self::GROUP_BY_DAY:
                $data = $model->getSummaryByDateBoundsAndCategoryGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->id
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getSummaryByDateBoundsAndCategoryGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->id
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

                $graphData->columns[$dateDatum['hash']][$date] = (float)abs($dateDatum['summary']);
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
                    $graphData->filterDto->id
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->id
                );
                break;
        }

        $initialBalance = 0;
        if ($graphData->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
            /** @var cashAccountModel $accountModel */
            $accountModel = cash()->getModel(cashAccount::class);
            $initialBalance = $accountModel->getStatDataForAccounts(
                '1970-01-01 00:00:00',
                $graphData->startDate->format('Y-m-d 23:59:59'),
                $graphData->filterDto->id
            );
        }

        foreach ($graphData->dates as $date) {
            if (!isset($data[$date])) {
                continue;
            }

            foreach ($data[$date] as $datum) {
                $accountId = $datum['category_id'];
                if (!$graphData->filterDto->id) {
                    $accountId = 'All accounts';
                }
                $graphData->lines[$accountId][$date] += ((float)$datum['summary'] + (float)$initialBalance[$datum['category_id']]['summary']);
            }
        }
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     */
    public function fillBalanceDataForCategories(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = [];
        switch ($this->determineGroup($graphData->startDate, $graphData->endDate)) {
            case self::GROUP_BY_DAY:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    [$graphData->filterDto->id]
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    [$graphData->filterDto->id]
                );
                break;
        }

        /** @var cashAccountModel $accountModel */
        $accountModel = cash()->getModel(cashAccount::class);
        $initialBalance = $accountModel->getStatDataForCategories(
            '1970-01-01 00:00:00',
            $graphData->startDate->format('Y-m-d 23:59:59'),
            [$graphData->filterDto->id]
        );

        foreach ($graphData->dates as $date) {
            if (!isset($data[$date])) {
                continue;
            }

            foreach ($data[$date] as $datum) {
                $categoryId = $datum['category_id'];
                $graphData->lines[$categoryId][$date] += ((float)$datum['summary'] + (float)$initialBalance[$datum['category_id']]['summary']);
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
//            $accountColumn = new cashStatOnDateDto($datum['account_id'], $datum['income'], $datum['expense'], $datum['summary']);
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