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
    public $types = [];

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
                $this->accounts = $model->getExistingAccountsBetweenDates(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59')
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
            $date = $iterateDate->format($this->grouping === cashGraphService::GROUP_BY_DAY ? 'Y-m-d' : 'Y-n');
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

        foreach ($existingCategories as $category) {
            $this->types[$category] = 'bar';
        }
        foreach ($this->accounts as $account) {
            $this->types[$account] = 'line';
        }
    }

    /**
     * @return array
     * @throws waException
     */
    public function jsonSerialize()
    {
        $columns = [array_merge(['dates'], $this->dates)];
        foreach ($this->columns as $name => $data) {
            $columns[] = array_values(array_merge([$name], $data));
        }
        foreach ($this->lines as $name => $data) {
            $columns[] = array_values(array_merge([$name], $data));
        }

        $colors = $names = $regions = [];

        $categories = cash()->getModel(cashCategory::class)->getAllActive();
        foreach ($this->categories as $hash => $category) {
            if ($category['id'] && isset($categories[$category['id']])) {
                $names[$hash] = sprintf('%s, %s', $categories[$category['id']]['name'], $category['currency']);
                $colors[$hash] = $categories[$category['id']]['color'];
            } else {
                $names[$hash] = sprintf('%s, %s', _w('No category'), $category['currency']);
                $colors[$hash] = cashColorStorage::DEFAULT_NO_CATEGORY_GRAPH_COLOR;
            }
        }

        if (in_array(
            $this->filterDto->type,
            [cashTransactionPageFilterDto::FILTER_ACCOUNT, cashTransactionPageFilterDto::FILTER_IMPORT],
            true
        )) {
            foreach ($this->lines as $lineId => $lineData) {
                $regions[$lineId] = [['start' => $this->currentDate, 'style' => 'dashed']];

//                if ($lineId !== self::ALL_ACCOUNTS_GRAPH_NAME) {
                $account = cash()->getModel(cashAccount::class)->getById($lineId);
                $names[$lineId] = sprintf('%s (%s)', $account['name'], $account['currency']);
                $colors[$lineId] = cashColorStorage::DEFAULT_ACCOUNT_GRAPH_COLOR;
//                }
            }
        }

        $data = [
            'data' => [
                'x' => 'dates',
                'columns' => $columns,
                'line' => ['connectNull' => true],
                'bar' => ['width' => ['ratio' => 0.2]],
                'types' => $this->types,
                'groups' => array_filter(
                    array_reduce(
                        $this->groups,
                        function ($groups, $items) {
                            return array_merge($groups, array_values($items));
                        },
                        []
                    )
                ),
                'regions' => $regions,
                'colors' => $colors,
                'names' => $names,
            ],
            'axis' => [
                'x' => ['type' => $this->grouping === cashGraphService::GROUP_BY_DAY ? 'timeseries' : 'category'],
            ],
            'grid' => [
                'y' => ['lines' => [['value' => 0]]],
            ],
            'line' => ['connectNull' => true],
        ];

        if ($this->endDate > $this->currentDate) {
            $data['grid']['x'] = [
                'lines' => [
                    [
                        'value' => $this->grouping === cashGraphService::GROUP_BY_DAY
                            ? $this->currentDate
                            : date('Y-n', strtotime($this->currentDate)),
                        'text' => _w('Future'),
                    ],
                ],
            ];
        }

        if ($this->grouping === cashGraphService::GROUP_BY_DAY) {
            $data['axis']['x']['tick'] = ['count' => 30, 'format' => '%Y-%m-%d'];
        }

        if ($this->lines) {
            $data['data']['axes'] = [];
            foreach ($this->types as $name => $type) {
                if ($type === 'bar') {
                    $data['data']['axes'][$name] = 'y';
                } else {
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
                    $data['data']['axes'][$name] = 'y2';
                    $data['axis']['y2'] = [
                        'show' => true,
//                        'center' => 0,
//                        'padding' => ['bottom' => 0],
                    ];
                }
            }

//            if (isset($data['axis']['y2'])) {
//                $extremum = ['min' => 0, 'max' => 0];
//                foreach ($this->lines as $lineData) {
//                    $extremum['min'] = min($extremum['min'], min(array_filter($lineData)));
//                    $extremum['max'] = max($extremum['max'], max(array_filter($lineData)));
//                }
////                $data['axis']['y2']['center'] = 250;
////                $data['axis']['y']['min'] =
//                $data['axis']['y2']['min'] = $extremum['min'];// - 250;
//            }
        }

        return $data;
    }
}
