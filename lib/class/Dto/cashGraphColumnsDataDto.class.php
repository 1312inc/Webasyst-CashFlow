<?php

/**
 * Class cashGraphColumnDto
 */
class cashGraphColumnsDataDto extends cashAbstractDto
{
    const ALL_ACCOUNTS_GRAPH_NAME = 'All accounts';
    const ALL_ACCOUNTS = -1;

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
     * cashGraphColumnsDataDto constructor.
     *
     * @param DateTime                     $startDate
     * @param DateTime                     $endDate
     * @param cashTransactionPageFilterDto $filterDto
     *
     * @throws waException
     */
    public function __construct(DateTime $startDate, DateTime $endDate, cashTransactionPageFilterDto $filterDto)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterDto = $filterDto;

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
                    $accounts = $model->getExistingAccountsBetweenDates(
                        $startDate->format('Y-m-d 00:00:00'),
                        $endDate->format('Y-m-d 23:59:59')
                    );
                } else {
                    $accounts = [$this->filterDto->id];
                }
                break;

            case cashTransactionPageFilterDto::FILTER_CATEGORY:
                $existingCategories = $model->getCategoriesAndCurrenciesHashByCategory(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $this->filterDto->id
                );
                $accounts = [];
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
            $date = $iterateDate->format('Y-m-d');
            $this->dates[] = $date;
            foreach ($existingCategories as $category) {
                $this->columns[$category][$date] = null;
            }
            foreach ($accounts as $account) {
                $this->lines[$account][$date] = null;
            }
            $iterateDate->modify('+1 day');
        }

        foreach ($existingCategories as $category) {
            $this->types[$category] = 'bar';
        }
        foreach ($accounts as $account) {
            $this->types[$account] = 'line';
        }
    }

    /**
     * @return array
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

        $colors = $names = $regions =  [];

        $categories = cash()->getModel(cashCategory::class)->getAllActive();
        foreach ($this->categories as $hash => $category) {
            if ($category['id'] && isset($categories[$category['id']])) {
                $names[$hash] = sprintf('%s, %s', $categories[$category['id']]['name'], $category['currency']);
                $colors[$hash] = $categories[$category['id']]['color'];
            }
            else {
                $names[$hash] = sprintf('%s, %s', _w('No category'), $category['currency']);
                $colors[$hash] = cashColorStorage::DEFAULT_NO_CATEGORY_GRAPH_COLOR;
            }
        }

        if ($this->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
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
            'x' => 'dates',
            'columns' => $columns,
            'line' => ['connectNull' => true],
            'bar' => ['width' => ['ratio' => 0.2]],
            'types' => $this->types,
            'groups' => array_values($this->groups),
            'regions' => $regions,
            'colors' => $colors,
            'names' => $names,
        ];

        return $data;
    }
}
