<?php

/**
 * Class cashGraphService
 */
class cashGraphService
{
    const GROUP_BY_DAY   = 1;
    const GROUP_BY_MONTH = 2;

    const DEFAULT_CHART_PERIOD_NAME = 'default_chart_period';
    const DEFAULT_FORECAST_PERIOD_NAME = 'default_forecast_period';

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
            //new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, -12),
            new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, -3),
            new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, -5),
            new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, -10),
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
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 30),
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 90),
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 180),
            new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 365),
            new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, 2),
            new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, 3),
        ];
    }

    /**
     * @return cashGraphPeriodVO
     */
    public function getDefaultChartPeriod()
    {
        $stored = wa()->getUser()->getSettings(cashConfig::APP_ID, self::DEFAULT_CHART_PERIOD_NAME);
        if ($stored) {
            $stored = json_decode($stored, true);

            return new cashGraphPeriodVO($stored['type'], $stored['value']);
        }

        return new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -90);
    }

    /**
     * @return cashGraphPeriodVO
     */
    public function getDefaultForecastPeriod()
    {
        $stored = wa()->getUser()->getSettings(cashConfig::APP_ID, self::DEFAULT_FORECAST_PERIOD_NAME);
        if ($stored) {
            $stored = json_decode($stored, true);

            return new cashGraphPeriodVO($stored['type'], $stored['value']);
        }

        return new cashGraphPeriodVO(cashGraphPeriodVO::MONTH_PERIOD, 1);
    }

    /**
     * @param cashGraphPeriodVO $periodVO
     */
    public function saveForecastPeriodVo(cashGraphPeriodVO $periodVO)
    {
        wa()->getUser()->setSettings(cashConfig::APP_ID, self::DEFAULT_FORECAST_PERIOD_NAME, json_encode($periodVO));
    }

    /**
     * @param cashGraphPeriodVO $periodVO
     */
    public function saveChartPeriodVo(cashGraphPeriodVO $periodVO)
    {
        wa()->getUser()->setSettings(cashConfig::APP_ID, self::DEFAULT_CHART_PERIOD_NAME, json_encode($periodVO));
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
        if ($filterDto->type == cashTransactionPageFilterDto::FILTER_IMPORT) {
            $grouping = self::GROUP_BY_DAY;
        }

        if (!isset($grouping)) {
            $grouping = $this->determineGroup($startDate);
        }

        return new cashGraphColumnsDataDto($startDate, $endDate, $filterDto, $grouping);
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillColumnCategoriesDataForAccounts(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
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

            default:
                throw new kmwaLogicException('No graph grouping');
        }

        $this->fillGraphColumnsWithData($graphData, $data);
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillColumnCategoriesDataForImport(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = $model->getSummaryByDateBoundsAndImportGroupByDay(
            $graphData->startDate->format('Y-m-d 00:00:00'),
            $graphData->endDate->format('Y-m-d 23:59:59'),
            $graphData->filterDto->id
        );

        $this->fillGraphColumnsWithData($graphData, $data);
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillColumnCategoriesDataForCategories(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
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

            default:
                throw new kmwaLogicException('No graph grouping');
        }

        $this->fillGraphColumnsWithData($graphData, $data);
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillBalanceDataForAccounts(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
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

            default:
                throw new kmwaLogicException('No graph grouping');
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

//        $firstDot = true;
        foreach ($graphData->dates as $date) {
            if (!isset($data[$date])) {
//                if ($firstDot) {
                    foreach ($graphData->accounts as $accountId) {
                        $graphData->lines[$accountId][$date] += (float)$initialBalance[$accountId]['summary'];
                    }
//                    $firstDot = false;
//                }

                continue;
            }

            foreach ($graphData->accounts as $accountId) {
                $graphData->lines[$accountId][$date] = (float)$initialBalance[$accountId]['summary'];
            }

            foreach ($data[$date] as $datum) {
                $accountId = $datum['category_id'];
                if (!isset($graphData->lines[$accountId])) {
                    $graphData->lines[$accountId] = [];
                }
//                if (!$graphData->filterDto->id) {
//                    $accountId = 'All accounts';
//                    continue;
//                }
                $graphData->lines[$accountId][$date] += (float)$datum['summary'];
                $initialBalance[$datum['category_id']]['summary'] = $graphData->lines[$accountId][$date];
            }
        }
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillBalanceDataForCategories(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
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

            default:
                throw new kmwaLogicException('No graph grouping');
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

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillBalanceDataForImport(cashGraphColumnsDataDto $graphData)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = $model->getBalanceByDateBoundsAndImportGroupByDay(
            $graphData->startDate->format('Y-m-d 00:00:00'),
            $graphData->endDate->format('Y-m-d 23:59:59'),
            $graphData->filterDto->id
        );

        foreach ($graphData->dates as $date) {
            if (!isset($data[$date])) {
                continue;
            }

            foreach ($data[$date] as $datum) {
                $categoryId = $datum['category_id'];
                $graphData->lines[$categoryId][$date] += ((float)$datum['summary'] + 0);
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
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface|null $endDate
     *
     * @return int
     * @throws Exception
     */
    private function determineGroup(DateTimeInterface $startDate, DateTimeInterface $endDate = null)
    {
        if (!$endDate) {
            $endDate = new DateTime();
        }
        if ($startDate->diff($endDate)->days > 90) {
            return self::GROUP_BY_MONTH;
        }

        return self::GROUP_BY_DAY;
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     * @param array                   $data
     */
    private function fillGraphColumnsWithData(cashGraphColumnsDataDto $graphData, array $data)
    {
        foreach ($data as $date => $dateData) {
            foreach ($dateData as $dateDatum) {
                // grouping by currency
                $graphData->groups[$dateDatum['currency']] = ifset(
                    $graphData->groups[$dateDatum['currency']],
                    ['expense' => [], 'income' => []]
                );

                if (!$dateDatum['category_id']) {
                    $dateDatum['hash'] .= ('_' . $dateDatum['cd']);
                }

                if (!in_array($dateDatum['hash'], $graphData->groups[$dateDatum['currency']][$dateDatum['cd']])) {
                    $graphData->groups[$dateDatum['currency']][$dateDatum['cd']][] = $dateDatum['hash'];
                    $graphData->categories[$dateDatum['hash']] = [
                        'id' => $dateDatum['category_id'],
                        'currency' => $dateDatum['currency'],
                    ];
                }

                // для славной категории трансферов надо суммировать, потому что нет отдельно expense и income
                if ($dateDatum['category_id'] == cashCategoryFactory::TRANSFER_CATEGORY_ID) {
                    $graphData->columns[$dateDatum['hash']][$date] += (float)abs($dateDatum['summary']);
                } else {
                    $graphData->columns[$dateDatum['hash']][$date] = (float)abs($dateDatum['summary']);
                }
            }
        }
    }
}
