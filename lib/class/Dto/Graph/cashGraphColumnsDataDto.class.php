<?php

/**
 * Class cashGraphColumnDto
 */
class cashGraphColumnsDataDto extends cashAbstractDto
{
    /**
     * @var array
     */
    public $dates = [];

    /**
     * @var array
     */
    public $groups = [];

    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var array
     */
    public $lines = [];

    /**
     * @var string
     */
    public $currentDate;

    /**
     * @var cashTransactionPageFilterDto
     */
    public $filterDto;

    /**
     * @var DateTime
     */
    public $startDate;

    /**
     * @var DateTime
     */
    public $endDate;

    /**
     * @var array
     */
    public $categories = [];

    /**
     * @var int
     */
    public $grouping;

    /**
     * @var array
     */
    public $accounts = [];

    /**
     * cashGraphColumnsDataDto constructor.
     *
     * @param DateTime                     $startDate
     * @param DateTime                     $endDate
     * @param cashTransactionPageFilterDto $filterDto
     * @param int                          $grouping
     *
     * @throws waException
     */
    public function __construct(
        DateTime $startDate,
        DateTime $endDate,
        cashTransactionPageFilterDto $filterDto,
        $grouping
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterDto = $filterDto;
        $this->grouping = $grouping;
        $existingCategories = [];

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        switch ($this->filterDto->type) {
            case cashTransactionPageFilterDto::FILTER_ACCOUNT:
                $existingCategories = $model->getCategoriesAndCurrenciesHashByAccount(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $this->filterDto->id
                );
                if (empty($this->filterDto->id)) {
                    $this->accounts = $model->getExistingAccountsBetweenDates(
                        $startDate->format('Y-m-d 00:00:00'),
                        $endDate->format('Y-m-d 23:59:59')
                    );
                } else {
                    $this->accounts = [$this->filterDto->id];
                }
                break;

            case cashTransactionPageFilterDto::FILTER_IMPORT:
                $existingCategories = $model->getCategoriesAndCurrenciesHashByImport(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $this->filterDto->id
                );
                break;

            case cashTransactionPageFilterDto::FILTER_CATEGORY:
                $existingCategories = $model->getCategoriesAndCurrenciesHashByCategory(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $this->filterDto->id
                );
                $this->accounts = [];
                break;
        }

//        if ($this->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
//            $accounts =  empty($this->filterDto->id) ? ['All accounts'] : [$this->filterDto->id];
//        if ($this->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT && !empty($this->filterDto->id)) {
//            $accounts = [$this->filterDto->id];
//        }

        $this->currentDate = date('Y-m-d');
        $iterateDate = clone $startDate;
        while ($iterateDate <= $endDate) {
            $date = $iterateDate->format($this->grouping === cashGraphService::GROUP_BY_DAY ? 'Y-m-d' : 'Y-m');
            $this->dates[] = $date;
            foreach ($existingCategories as $category) {
                $this->columns[$category][$date] = null;
            }
            foreach ($this->accounts as $account) {
                $this->lines[$account][$date] = null;
            }
            $iterateDate->modify(
                sprintf('+1 %s', $this->grouping === cashGraphService::GROUP_BY_DAY ? 'day' : 'month')
            );
        }

//        foreach ($existingCategories as $category) {
//            $this->types[$category] = 'bar';
//        }
//        foreach ($this->accounts as $account) {
//            $this->types[$account] = 'line';
//        }
    }

    /**
     * @return array
     * @throws waException
     */
    public function jsonSerialize()
    {
        $colors = $names = $regions = $lineIds = $columns = $currencies = $types = $expenseCategories = $incomeCategories = [];

        if ($this->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
            $linesGroupedByCurrency = [];
            foreach ($this->lines as $lineId => $lineData) {
                $account = cash()->getModel(cashAccount::class)->getById($lineId);

                if ($this->filterDto->id) {
                    $names[$lineId] = $account['name'];
                    $colors[$lineId] = cashColorStorage::DEFAULT_ACCOUNT_GRAPH_COLOR;
                    $currencies[$lineId] = cashCurrencyVO::fromWaCurrency($account['currency']);
                } else {
                    if (!isset($linesGroupedByCurrency[$account['currency']])) {
                        $linesGroupedByCurrency[$account['currency']] = [];
                        $colors[$account['currency']] = cashColorStorage::DEFAULT_ACCOUNT_GRAPH_COLOR;
                    }

                    $currencies[$account['currency']] = cashCurrencyVO::fromWaCurrency($account['currency']);

                    foreach ($lineData as $date => $value) {
                        if (!isset($linesGroupedByCurrency[$account['currency']][$date])) {
                            $linesGroupedByCurrency[$account['currency']][$date] = 0;
                        }
                        $linesGroupedByCurrency[$account['currency']][$date] += $value;
//                        if ($linesGroupedByCurrency[$account['currency']][$date] == 0) {
//                            $linesGroupedByCurrency[$account['currency']][$date] = null;
//                        }
                    }
                }
            }

            if ($linesGroupedByCurrency) {
                $this->lines = $linesGroupedByCurrency;
            }

            $lineIds = array_keys($this->lines);
            foreach ($lineIds as $lineId) {
                $types[$lineId] = 'line';
            }
        }

        foreach ($this->columns as $name => $data) {
            if (in_array($name, $this->groups[$this->categories[$name]['currency']]['expense'])) {
                array_unshift($columns, array_values(array_merge([$name], $data)));
            } else {
                $columns[] = array_values(array_merge([$name], $data));
            }
            $types[$name] = 'bar';
        }
        array_unshift($columns, array_merge(['dates'], $this->dates));
        foreach ($this->lines as $name => $data) {
            $columns[] = array_values(array_merge([$name], $data));
        }

        $categories = cash()->getModel(cashCategory::class)->getAllActive();
        foreach ($this->categories as $hash => $category) {
            if ($category['id'] && isset($categories[$category['id']])) {
                $names[$hash] = $categories[$category['id']]['name'];
                $colors[$hash] = $categories[$category['id']]['color'];
            } else {
                $names[$hash] = sprintf(
                    '%s, %s',
                    _w('No category'),
                    in_array($hash, $this->groups[$category['currency']]['expense']) ? _w('expense') : _w('income')
                );
                $colors[$hash] = cashCategoryFactory::NO_CATEGORY_COLOR;
            }
        }

        $xFormat = $this->grouping === cashGraphService::GROUP_BY_DAY ? '%Y-%m-%d' : '%Y-%m';

        $groups = array_filter(
            array_reduce(
                $this->groups,
                function ($groups, $items) {
                    return array_merge($groups, array_values($items));
                },
                []
            )
        );

        foreach ($this->groups as $currencyCode => $dataNames) {
            foreach ($dataNames as $expenseOrIncome => $dataName) {
                foreach ($dataName as $item) {
                    if ($expenseOrIncome === 'expense') {
                        $expenseCategories = $dataName;
                    }
                    if ($expenseOrIncome === 'income') {
                        $incomeCategories = $dataName;
                    }

                    $currencies[$item] = cashCurrencyVO::fromWaCurrency($currencyCode);
                }
            }
        }

        $data = [
            'data' => [
                'x' => 'dates',
                'xFormat' => $xFormat,
                'columns' => $columns,
                'order' => null,
                'line' => ['connectNull' => true],
                'bar' => ['width' => ['ratio' => 0.2]],
                'types' => $types,
                'groups' => $groups,
                'colors' => $colors,
                'names' => $names,
            ],
            'axis' => [
                'x' => [
                    'type' => 'timeseries',
                    'tick' => [
                        'count' => 4,
                        'format' => '%Y-%m-%d',
                    ],
                ],
            ],
            'grid' => [
                'y' => ['lines' => [['value' => 0]]],
            ],
            'line' => ['connectNull' => true],
            'helpers' => [
                'lineIds' => $lineIds,
                'itemsCount' => count($this->dates),
                'currencyNames' => $currencies,
            ],
        ];

        if ($this->endDate->format('Y-m-d') > $this->currentDate) {
            $start = $this->grouping === cashGraphService::GROUP_BY_DAY
                ? $this->currentDate
                : date('Y-n', strtotime($this->currentDate));
            $data['grid']['x'] = [
                'lines' => [
                    [
                        'value' => $start,
                        'text' => _w('Today'),
                    ],
                ],
            ];
            $data['regions'] = [
                ['axis' => 'x', 'start' => $start, 'class' => 'cash-c3-future'],
            ];
        }

        $tickData = ['rotate' => -45];
        $tickCountDiff = $this->startDate->diff($this->endDate);
        $tickStartDate = clone $this->startDate;
        if ($this->grouping === cashGraphService::GROUP_BY_DAY) {
            $tickValues[] = $tickStartDate->format('Y-m-d');
            $ticksInterval = max(round($tickCountDiff->days / 25), 1);
            while ($tickStartDate <= $this->endDate) {
                $tickValues[] = $tickStartDate->modify("+{$ticksInterval} days")->format('Y-m-d');
            }
            $tickData['format'] = '%Y-%m-%d';
        } else {
            $tickValues[] = $tickStartDate->format('Y-m');
            while ($tickStartDate <= $this->endDate) {
                $tickValues[] = $tickStartDate->modify("+1 month")->format('Y-m');
            }
            $tickData['format'] = '%b %Y';
        }
        $tickData['values'] = $tickValues;
        $data['axis']['x']['tick'] = $tickData;

        if ($this->lines) {
            $data['data']['axes'] = [];
            $hasLines = false;
            foreach ($types as $name => $type) {
                if ($type === 'bar') {
                    $data['data']['axes'][$name] = 'y';
                } else {
                    $hasLines = true;
                    $data['data']['axes'][$name] = 'y2';
                }
            }

            if ($hasLines) {
                $data['grid']['y'] = [
                    'lines' => [
                        [
                            'value' => 0,
                            'axis' => 'y2',
                            'position' => 'start',
                            'text' => _w('Account balance zero'),
                        ],
                    ],
                ];
                $data['axis']['y2'] = [
                    'show' => true,
                    'label' => _w('Balance'),
                ];
                $data['axis']['y'] = [
                    'show' => true,
                ];
            }

            if (isset($data['axis']['y2'])) {
                $extremum = [
                    'lines' => ['min' => PHP_INT_MAX, 'max' => PHP_INT_MIN],
                    'columns' => ['min' => PHP_INT_MAX, 'max' => PHP_INT_MIN],
                ];

                foreach ($this->lines as $lineData) {
                    $notNullData = array_filter($lineData);
                    if ($notNullData) {
                        $extremum['lines']['min'] = min($extremum['lines']['min'], min($notNullData));
                        $extremum['lines']['max'] = max($extremum['lines']['max'], max($notNullData));
                    }
                }

                foreach ($this->columns as $columnName => $columnData) {
                    $notNullData = array_filter($columnData);
                    if ($notNullData) {
                        $extremum['columns']['min'] = min($extremum['columns']['min'], min($notNullData));
                        $extremum['columns']['max'] = max($extremum['columns']['max'], max($notNullData));
                        if (in_array($columnName, $expenseCategories, true)) {
                            $notNullData = array_map(function ($v) { return -$v; }, $notNullData);
                            $extremum['columns']['min'] = min($extremum['columns']['min'], min($notNullData));
                        }
                    }
                }
                $extremum['columns']['min'] = min(0, $extremum['columns']['min']);
                $ration = [
                    'mins' => $extremum['columns']['min']
                        ? $extremum['lines']['min'] / $extremum['columns']['min']
                        : 1,
                    'maxs' => $extremum['lines']['max'] / $extremum['columns']['max'],

                    'abs' => (abs($extremum['lines']['min']) + abs($extremum['lines']['max'])) / (abs($extremum['columns']['min']) + abs( $extremum['columns']['max'])),

                    'lines' => $extremum['lines']['min']
                        ? abs($extremum['lines']['max']) / abs($extremum['lines']['min'])
                        : 1,
                    'columns' => $extremum['columns']['min']
                        ? abs($extremum['columns']['max']) / abs($extremum['columns']['min'])
                        : 1,
                ];

                if ($extremum['lines']['min'] >= 0) {
                    $data['axis']['y']['min'] = 0;
                    $data['axis']['y']['max'] = $extremum['columns']['max'];

                    $data['axis']['y2']['min'] = 0;
                    $data['axis']['y2']['max'] = $extremum['lines']['max'];
                } elseif ($extremum['lines']['max'] < 0) {
                    $data['axis']['y']['min'] = $extremum['lines']['min'];
                    $data['axis']['y']['max'] = $extremum['columns']['max'];

                    $data['axis']['y2']['min'] = $extremum['lines']['min'];
                    $data['axis']['y2']['max'] = $extremum['columns']['max'];
                } else {
                    $data['axis']['y']['min'] = -abs($extremum['lines']['min']/$ration['maxs']);
                    $data['axis']['y']['max'] = $extremum['columns']['max'];

                    $data['axis']['y2']['min'] = $extremum['lines']['min'];
                    $data['axis']['y2']['max'] = $extremum['lines']['max'];
                }

                $data['helpers']['extremum'] = $extremum;
                $data['helpers']['ratio'] = $ration;
            }
        }

//        if (empty($this->columns)) {
//            $data['empty'] = true;
//        }

        $data['legend'] = ['hide' => array_keys($this->columns)];

        return $data;
    }
}
