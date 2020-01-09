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
     * @var array
     */
    public $accountIds;

    /**
     * @var DateTime
     */
    public $startDate;

    /**
     * @var DateTime
     */
    public $endDate;

    /**
     * cashGraphColumnsDataDto constructor.
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param array    $accounts
     *
     * @throws waException
     */
    public function __construct(DateTime $startDate, DateTime $endDate, array $accounts)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $existingCategories = $model->getCategoriesAndCurrenciesHash(
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59'),
            $accounts
        );

        $this->accountIds = $accounts;
        if ($accounts === []) {
            $accounts = ['All accounts'];
        }

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

        $regions = [];
        foreach ($this->lines as $lineId => $lineData) {
            $regions[$lineId] = [['start' => $this->currentDate, 'style' => 'dashed']];
        }

        $colors = [];

        $data = [
            'x' => 'dates',
            'columns' => $columns,
            'line' => ['connectNull' => true],
            'bar' => ['width' => ['ratio' => 0.2]],
            'types' => $this->types,
            'groups' => array_values($this->groups),
            'regions' => $regions,
            'colors' => $colors,
        ];

        return $data;
    }
}
