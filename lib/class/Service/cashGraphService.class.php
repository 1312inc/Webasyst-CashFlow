<?php

/**
 * Class cashGraphService
 */
class cashGraphService
{
    const GROUP_BY_DAY   = 1;
    const GROUP_BY_MONTH = 2;

    const DEFAULT_CHART_PERIOD_NAME    = 'default_chart_period';
    const DEFAULT_FORECAST_PERIOD_NAME = 'default_forecast_period';

    /**
     * @return array
     */
    public static function getChartPeriods(): array
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
//            new cashGraphPeriodVO(cashGraphPeriodVO::ALL_TIME_PERIOD),
        ];
    }

    /**
     * @return array
     */
    public static function getForecastPeriods(): array
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
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function getDefaultChartPeriod(): cashGraphPeriodVO
    {
        $stored = wa()->getUser()->getSettings(cashConfig::APP_ID, self::DEFAULT_CHART_PERIOD_NAME);
        if ($stored) {
            $stored = json_decode($stored, true);

            return new cashGraphPeriodVO($stored['type'], $stored['value']);
        }

        return new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, -365);
    }

    /**
     * @return cashGraphPeriodVO
     * @throws kmwaRuntimeException
     */
    public function getDefaultForecastPeriod(): cashGraphPeriodVO
    {
        $stored = wa()->getUser()->getSettings(cashConfig::APP_ID, self::DEFAULT_FORECAST_PERIOD_NAME);
        if ($stored) {
            $stored = json_decode($stored, true);

            return new cashGraphPeriodVO($stored['type'], $stored['value']);
        }

        return new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 180);
    }

    /**
     * @param cashGraphPeriodVO $periodVO
     */
    public function saveForecastPeriodVo(cashGraphPeriodVO $periodVO): void
    {
        wa()->getUser()->setSettings(cashConfig::APP_ID, self::DEFAULT_FORECAST_PERIOD_NAME, json_encode($periodVO));
    }

    /**
     * @param cashGraphPeriodVO $periodVO
     */
    public function saveChartPeriodVo(cashGraphPeriodVO $periodVO): void
    {
        wa()->getUser()->setSettings(cashConfig::APP_ID, self::DEFAULT_CHART_PERIOD_NAME, json_encode($periodVO));
    }

    /**
     * @param DateTimeInterface $dateTime
     *
     * @return cashGraphPeriodVO
     * @throws Exception
     */
    public function getChartPeriodByDate(DateTimeInterface $dateTime): cashGraphPeriodVO
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
    public function getForecastPeriodByDate(DateTimeInterface $dateTime): cashGraphPeriodVO
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
    public function createDto(
        DateTime $startDate,
        DateTime $endDate,
        cashTransactionPageFilterDto $filterDto
    ): cashGraphColumnsDataDto {
        if ($filterDto->type == cashTransactionPageFilterDto::FILTER_IMPORT) {
            $grouping = self::GROUP_BY_DAY;
        }

        if (!isset($grouping)) {
            $grouping = $this->determineGroup($startDate, $endDate);
        }

        return new cashGraphColumnsDataDto($startDate, $endDate, $filterDto, $grouping);
    }

    /**
     * @param cashGraphColumnsDataDto $graphData
     *
     * @throws waException
     * @throws kmwaLogicException
     */
    public function fillColumnCategoriesDataForAccounts(cashGraphColumnsDataDto $graphData): void
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
            case self::GROUP_BY_DAY:
                $data = $model->getSummaryByDateBoundsAndAccountGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
                    $graphData->filterDto->id
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getSummaryByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
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
     */
    public function fillColumnCategoriesDataForImport(cashGraphColumnsDataDto $graphData): void
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $data = $model->getSummaryByDateBoundsAndImportGroupByDay(
            $graphData->startDate->format('Y-m-d 00:00:00'),
            $graphData->endDate->format('Y-m-d 23:59:59'),
            $graphData->filterDto->contact,
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
    public function fillColumnCategoriesDataForCategories(cashGraphColumnsDataDto $graphData): void
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
            case self::GROUP_BY_DAY:
                $data = $model->getSummaryByDateBoundsAndCategoryGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
                    $graphData->filterDto->id
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getSummaryByDateBoundsAndCategoryGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
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
    public function fillBalanceDataForAccounts(cashGraphColumnsDataDto $graphData): void
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
            case self::GROUP_BY_DAY:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
                    $graphData->filterDto->id
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
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
                $graphData->filterDto->contact,
                [$graphData->filterDto->id]
            );
        }

        foreach ($graphData->dates as $date) {
            if (!isset($data[$date])) {
                foreach ($graphData->accounts as $accountId) {
                    if (!isset($initialBalance[$accountId])) {
                        continue;
                    }

                    $graphData->lines[$accountId][$date] += (float) $initialBalance[$accountId]['summary'];
                }

                continue;
            }

            foreach ($graphData->accounts as $accountId) {
                if (!isset($initialBalance[$accountId])) {
                    continue;
                }

                $graphData->lines[$accountId][$date] = (float) $initialBalance[$accountId]['summary'];
            }

            foreach ($data[$date] as $datum) {
                $accountId = $datum['category_id'];

                if (!isset($initialBalance[$accountId])) {
                    continue;
                }

                if (!isset($graphData->lines[$accountId])) {
                    $graphData->lines[$accountId] = [];
                }
                $graphData->lines[$accountId][$date] += (float) $datum['summary'];
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
    public function fillBalanceDataForCategories(cashGraphColumnsDataDto $graphData): void
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        switch ($graphData->grouping) {
            case self::GROUP_BY_DAY:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByDay(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
                    [$graphData->filterDto->id]
                );
                break;

            case self::GROUP_BY_MONTH:
                $data = $model->getBalanceByDateBoundsAndAccountGroupByMonth(
                    $graphData->startDate->format('Y-m-d 00:00:00'),
                    $graphData->endDate->format('Y-m-d 23:59:59'),
                    $graphData->filterDto->contact,
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
                $graphData->lines[$categoryId][$date] += ((float) $datum['summary'] + (float) $initialBalance[$datum['category_id']]['summary']);
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
                $graphData->lines[$categoryId][$date] += ((float) $datum['summary'] + 0);
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
     * @param DateTimeInterface      $startDate
     * @param DateTimeInterface|null $endDate
     *
     * @return int
     * @throws Exception
     */
    private function determineGroup(DateTimeInterface $startDate, DateTimeInterface $endDate = null): int
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
    private function fillGraphColumnsWithData(cashGraphColumnsDataDto $graphData, array $data): void
    {
        foreach ($data as $date => $dateData) {
            foreach ($dateData as $dateDatum) {
                // grouping by currency
                $graphData->groups[$dateDatum['currency']] = ifset(
                    $graphData->groups[$dateDatum['currency']],
                    ['expense' => [], 'income' => []]
                );

//                if (!$dateDatum['category_id']) {
//                    $dateDatum['hash'] .= ('_' . $dateDatum['cd']);
//                }

                if (!in_array($dateDatum['hash'], $graphData->groups[$dateDatum['currency']][$dateDatum['cd']])) {
                    $graphData->groups[$dateDatum['currency']][$dateDatum['cd']][] = $dateDatum['hash'];
                    $graphData->categories[$dateDatum['hash']] = [
                        'id' => $dateDatum['category_id'],
                        'currency' => $dateDatum['currency'],
                    ];
                }

                // для славной категории трансферов надо суммировать, потому что нет отдельно expense и income
                if ($dateDatum['category_id'] == cashCategoryFactory::TRANSFER_CATEGORY_ID) {
                    $graphData->columns[$dateDatum['hash']][$date] += (float) abs($dateDatum['summary']);
                } else {
                    $graphData->columns[$dateDatum['hash']][$date] = (float) abs($dateDatum['summary']);
                }
            }
        }
    }

    /**
     * @param cashAggregateChartDataFilterParamsDto $paramsDto
     *
     * @return array
     * @throws waException
     */
    public function getAggregateChartData(cashAggregateChartDataFilterParamsDto $paramsDto): array
    {
        $grouping = $this->getGroupingSqlDateFormat($paramsDto);
        $format = $this->getGroupingDateFormat($paramsDto);

        $sqlParts = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'account_access' => cash()->getContactRights()->getSqlForFilterTransactionsByAccount(
                        $paramsDto->contact
                    ),
                    'category_access' => cash()->getContactRights()->getSqlForCategoryJoin(
                        $paramsDto->contact,
                        'ct',
                        'category_id'
                    ),
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                ]
            )
            ->join(
                [
                    'join cash_account ca on ct.account_id = ca.id',
                    'join cash_category cc on ct.category_id = cc.id',
                ]
            )
            ->select(
                [
                    "{$grouping} groupkey",
                    'ca.currency currency',
                    'null balance',
                ]
            );

        $calculateBalance = false;
        switch (true) {
            case null !== $paramsDto->filter->getAccountId():
                $sqlParts->addAndWhere('ct.account_id = i:account_id')
                    ->addParam('account_id', $paramsDto->filter->getAccountId())
                    ->addSelect('sum(if(ct.amount < 0, 0, ct.amount)) incomeAmount')
                    ->addSelect('sum(if(ct.amount < 0, ct.amount, 0)) expenseAmount');

                if (cash()->getContactRights()->canSeeAccountBalance(
                    $paramsDto->contact,
                    $paramsDto->filter->getAccountId()
                )) {
                    $calculateBalance = true;
                }

                break;

            case null !== $paramsDto->filter->getCategoryId():
                $sqlParts->addAndWhere('ct.category_id = i:category_id')
                    ->addParam('category_id', $paramsDto->filter->getCategoryId())
                    ->addSelect('sum(if(ct.amount < 0, null, ct.amount)) incomeAmount')
                    ->addSelect('sum(if(ct.amount < 0, ct.amount, null)) expenseAmount');

                break;

            case null !== $paramsDto->filter->getContractorId():
                $sqlParts->addAndWhere('ct.contractor_contact_id = i:contractor_contact_id')
                    ->addParam('contractor_contact_id', $paramsDto->filter->getContractorId())
                    ->addSelect('sum(if(ct.amount < 0, 0, ct.amount)) incomeAmount')
                    ->addSelect('sum(if(ct.amount < 0, ct.amount, 0)) expenseAmount');

                break;

            case null !== $paramsDto->filter->getCurrency():
                $sqlParts->addAndWhere('ca.currency = s:currency')
                    ->addParam('currency', $paramsDto->filter->getCurrency())
                    ->addSelect('sum(if(ct.amount < 0, 0, ct.amount)) incomeAmount')
                    ->addSelect('sum(if(ct.amount < 0, ct.amount, 0)) expenseAmount');

                $accountSql = clone $sqlParts;
                $accounts = $accountSql->select(['ct.account_id'])
                    ->groupBy(['ct.account_id'])
                    ->query()
                    ->fetchAll('account_id');

                foreach ($accounts as $accountId => $accountIds) {
                    if (cash()->getContactRights()->canSeeAccountBalance($paramsDto->contact, $accountId)) {
                        $calculateBalance = true;
                    } else {
                        $calculateBalance = false;
                        break;
                    }
                }

                break;
        }

        $initialBalance = 0;
        if ($calculateBalance) {
            $initialSql = clone $sqlParts;
            $initialData = $initialSql->select(['sum(ct.amount) balance'])
                ->addAndWhere('ct.date < s:from')
                ->addParam('from', $paramsDto->from->format('Y-m-d'))
                ->query()
                ->fetchAll();

            $initialBalance = (float) $initialData[0]['balance'];
        }

        $data = $sqlParts->addAndWhere(sprintf('%s between s:from and s:to', $grouping))
            ->addParam('from', $paramsDto->from->format($format))
            ->addParam('to', $paramsDto->to->format($format))
            ->groupBy(['groupkey', 'currency'])
            ->orderBy(['groupkey'])
            ->query()
            ->fetchAll();

        if ($calculateBalance) {
            foreach ($data as $i => $datum) {
                $data[$i]['balance'] = (float) $data[$i]['incomeAmount']
                    + (float) $data[$i]['expenseAmount']
                    + $initialBalance;
                $initialBalance = $data[$i]['balance'];
            }
        }

        return $data;
    }

    /**
     * @param cashAggregateChartDataFilterParamsDto $paramsDto
     *
     * @return array
     * @throws waException
     */
    public function getAggregateBalanceFlow(cashAggregateChartDataFilterParamsDto $paramsDto): array
    {
        $accounts = cash()->getEntityRepository(cashAccount::class)
            ->findAllActiveFullAccessForContact($paramsDto->contact);

        $currencies = [];
        /** @var cashAccount $account */
        foreach ($accounts as $account) {
            $currencies[$account->getCurrency()] = [];
        }

        $sqlParts = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'account_access' => cash()->getContactRights()->getSqlForAccountJoinWithFullAccess(
                        $paramsDto->contact
                    ),
//                    'category_access' => cash()->getContactRights()->getSqlForCategoryJoin(
//                        $paramsDto->contact,
//                        'ct',
//                        'category_id'
//                    ),
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                ]
            )
            ->join(
                [
                    'join cash_account ca on ct.account_id = ca.id',
//                    'join cash_category cc on ct.category_id = cc.id',
                ]
            );

        $initialBalanceSql = clone $sqlParts;
        $initialBalanceSql->select(['ca.currency currency, sum(ct.amount) balance'])
            ->addAndWhere('ct.date < s:from')
            ->addParam('from', $paramsDto->from->format('Y-m-d H:i:s'))
            ->groupBy(['ca.currency']);

        $initialBalance = array_map('floatval', $initialBalanceSql->query()->fetchAll('currency', 1));

        $grouping = $this->getGroupingSqlDateFormat($paramsDto);
        $format = $this->getGroupingDateFormat($paramsDto);

        $sqlParts->addAndWhere(sprintf('%s between s:from and s:to', $grouping))
            ->select(
                [
                    'ca.currency currency',
                    "{$grouping} period",
                    'sum(ct.amount) amount',
                ]
            )
            ->addParam('from', $paramsDto->from->format($format))
            ->addParam('to', $paramsDto->to->format($format))
            ->groupBy(['currency', 'period'])
            ->orderBy(['currency', 'period']);

        $data = $sqlParts->query()->fetchAll('currency', 2);
        foreach ($currencies as $currencyCode => $currency) {
            if (!isset($data[$currencyCode])) {
                continue;
            }

            $currencies[$currencyCode] = array_map(
                static function ($datum) use ($currencyCode, &$initialBalance) {
                    if (!isset($initialBalance[$currencyCode])) {
                        $initialBalance[$currencyCode] = 0;
                    }
                    $datum['amount'] = (float) $datum['amount'] + $initialBalance[$currencyCode];
                    $initialBalance[$currencyCode] = (float) $datum['amount'];

                    return $datum;
                },
                $data[$currencyCode]
            );
        }

        return $currencies;
    }

    public function getGroupingDateFormat(cashAggregateChartDataFilterParamsDto $paramsDto): string
    {
        switch ($paramsDto->groupBy) {
            case cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY:
                return 'Y-m-d';

            case cashAggregateChartDataFilterParamsDto::GROUP_BY_YEAR:
                return 'Y';

            case cashAggregateChartDataFilterParamsDto::GROUP_BY_MONTH:
            default:
                return 'Y-m';
        }
    }

    public function getGroupingSqlDateFormat(
        cashAggregateChartDataFilterParamsDto $paramsDto,
        string $prefix = 'ct'
    ): string {
        switch ($paramsDto->groupBy) {
            case cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY:
                return $prefix . '.date';

            case cashAggregateChartDataFilterParamsDto::GROUP_BY_YEAR:
                return "date_format({$prefix}.date, '%Y')";

            case cashAggregateChartDataFilterParamsDto::GROUP_BY_MONTH:
            default:
                return "date_format({$prefix}.date, '%Y-%m')";
        }
    }

    /**
     * @param cashAggregateGetBreakDownFilterParamsDto $paramsDto
     *
     * @return array
     * @throws waException
     */
    public function getAggregateBreakDownData(cashAggregateGetBreakDownFilterParamsDto $paramsDto): array
    {
        $detailing = '';
        switch ($paramsDto->detailsBy) {
            case cashAggregateGetBreakDownFilterParamsDto::DETAILS_BY_CATEGORY:
                $detailing = 'ct.category_id';

                break;

            case cashAggregateGetBreakDownFilterParamsDto::DETAILS_BY_CONTACT:
                $detailing = 'ct.contractor_contact_id';
                break;

//            case null !== $paramsDto->currency:
//                $sqlWhereAnd[] = 'ca.currency = s:currency';
//                $queryParams['currency'] = $paramsDto->currency;
//
//                $accountsSql = str_replace(
//                    ['__SELECT__', '__WHERE__', '__GROUP_BY__', '__ORDER_BY__'],
//                    ['ct.account_id', implode(' and ', $sqlWhereAnd), 'group by ct.account_id', ''],
//                    $basicSql
//                );
//
//                $accounts = $model->query($accountsSql, $queryParams)->fetchAll('account_id');
//                foreach ($accounts as $accountId) {
//                    if (cash()->getContactRights()->canSeeAccountBalance($paramsDto->contact, $accountId)) {
//                        $calculateBalance = true;
//                    } else {
//                        $calculateBalance = false;
//                        break;
//                    }
//                }
//                break;
        }

        $sqlParts = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->select(
                [
                    "if(ct.amount < 0, 'expense', 'income') `type`",
                    'ca.currency currency',
                    "{$detailing} detailed",
                    'sum(ct.amount) amount',
                ]
            )
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'ct.date between s:from and s:to',
                    'ct.is_archived = 0',
                    'account_access' => cash()->getContactRights()->getSqlForFilterTransactionsByAccount(
                        $paramsDto->contact
                    ),
                    'category_access' => cash()->getContactRights()->getSqlForCategoryJoin(
                        $paramsDto->contact,
                        'ct',
                        'category_id'
                    ),
                    'ca.is_archived = 0',
                ]
            )
            ->join(
                [
                    'join cash_account ca on ct.account_id = ca.id',
                    'join cash_category cc on ct.category_id = cc.id',
                ]
            )
            ->groupBy(['`type`', 'ca.currency', 'detailed'])
            ->params(['from' => $paramsDto->from->format('Y-m-d'), 'to' => $paramsDto->to->format('Y-m-d')]);

        switch (true) {
            case null !== $paramsDto->filter->getAccountId():
                $sqlParts->addAndWhere('ct.account_id = i:account_id')
                    ->addParam('account_id', $paramsDto->filter->getAccountId());

                break;

            case null !== $paramsDto->filter->getCategoryId():
                $sqlParts->addAndWhere('ct.category_id = i:category_id')
                    ->addParam('category_id', $paramsDto->filter->getCategoryId());

                break;

            case null !== $paramsDto->filter->getContractorId():
                $sqlParts->addAndWhere('ct.contractor_contact_id = i:contractor_contact_id')
                    ->addParam('contractor_contact_id', $paramsDto->filter->getContractorId());

                break;

            case null !== $paramsDto->filter->getCurrency():
                $sqlParts->addAndWhere('ca.currency = s:currency')
                    ->addParam('currency', $paramsDto->filter->getCurrency());

                break;
        }

        return $sqlParts->query()->fetchAll();
    }

    /**
     * @param cashAggregateChartDataFilterParamsDto $paramsDto
     *
     * @return array
     * @throws waException
     */
    public function getAggregateChartBalance(cashAggregateChartDataFilterParamsDto $paramsDto): array
    {
        $model = cash()->getModel(cashTransaction::class);
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($paramsDto->contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin(
            $paramsDto->contact,
            'ct',
            'category_id'
        );

        $basicSql = <<<SQL
select
__SELECT__
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id
join cash_category cc on ct.category_id = cc.id
where 
__WHERE__
__GROUP_BY__
__ORDER_BY__
SQL;

        $whereAnd = [$accountAccessSql, $categoryAccessSql, 'ct.is_archived = 0', 'ca.is_archived = 0'];
        $select = [];

        $queryParams = [];

        $calculateBalance = false;
        switch (true) {
            case null !== $paramsDto->filter->getAccountId():
                $whereAnd[] = 'ct.account_id = i:account_id';
                $queryParams['account_id'] = $paramsDto->filter->getAccountId();
                if (cash()->getContactRights()->canSeeAccountBalance(
                    $paramsDto->contact,
                    $paramsDto->filter->getAccountId()
                )) {
                    $calculateBalance = true;
                }

                break;

            case null !== $paramsDto->filter->getCurrency():
                $whereAnd[] = 'ca.currency = s:currency';
                $queryParams['currency'] = $paramsDto->filter->getCurrency();

                $accountsSql = str_replace(
                    ['__SELECT__', '__WHERE__', '__GROUP_BY__', '__ORDER_BY__'],
                    ['ct.account_id', implode(' and ', $whereAnd), 'group by ct.account_id', ''],
                    $basicSql
                );

                $accounts = $model->query($accountsSql, $queryParams)->fetchAll('account_id');
                foreach ($accounts as $accountId) {
                    if (cash()->getContactRights()->canSeeAccountBalance($paramsDto->contact, $accountId)) {
                        $calculateBalance = true;
                    } else {
                        $calculateBalance = false;
                        break;
                    }
                }
                break;
        }

        if (!$calculateBalance) {
            return [];
        }

        $initialBalanceSql = str_replace(
            ['__SELECT__', '__WHERE__', '__GROUP_BY__', '__ORDER_BY__'],
            [
                'sum(ct.amount) balance',
                implode(' and ', $whereAnd + ['ct.data < s:from']),
                '',
                '',
            ],
            $basicSql
        );

        $initialData = $model->query($initialBalanceSql, $queryParams)->fetchAll();
        $initialBalance = (float) $initialData[0]['balance'];

        switch ($paramsDto->groupBy) {
            case cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY:
                $grouping = 'ct.date';
                $whereAnd[] = 'ct.date between s:from and s:to';
                $queryParams['from'] = $paramsDto->from->format('Y-m-d');
                $queryParams['to'] = $paramsDto->to->format('Y-m-d');

                break;

            case cashAggregateChartDataFilterParamsDto::GROUP_BY_YEAR:
                $grouping = "date_format(ct.date, '%Y')";
                $whereAnd[] = "date_format(ct.date, '%Y') between s:from and s:to";
                $queryParams['from'] = $paramsDto->from->format('Y');
                $queryParams['to'] = $paramsDto->to->format('Y');

                break;

            case cashAggregateChartDataFilterParamsDto::GROUP_BY_MONTH:
            default:
                $grouping = "date_format(ct.date, '%Y-%m')";
                $whereAnd[] = "date_format(ct.date, '%Y-%m') between s:from and s:to";
                $queryParams['from'] = $paramsDto->from->format('Y-m');
                $queryParams['to'] = $paramsDto->to->format('Y-m');

                break;
        }

        $select = [
            "{$grouping} groupkey",
            'sum(ct.amount) balance',
        ];

        $dataSql = str_replace(
            ['__SELECT__', '__WHERE__', '__GROUP_BY__', '__ORDER_BY__'],
            [
                implode(',', $select),
                implode(' and ', $whereAnd),
                'group by groupkey',
                'order by groupkey',
            ],
            $basicSql
        );

        $data = $model->query($dataSql, $queryParams)->fetchAll();
        foreach ($data as $i => $datum) {
            $data[$i]['balance'] = (float) $data[$i]['balance'] + $initialBalance;
        }

        return $data;
    }
}
