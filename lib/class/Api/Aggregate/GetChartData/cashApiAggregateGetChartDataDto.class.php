<?php

/**
 * Class cashApiAggregateGetChartDataDto
 */
class cashApiAggregateGetChartDataDto
{
    /**
     * @var float
     */
    public $amountIncome;

    /**
     * @var float
     */
    public $amountExpense;

    /**
     * @var float|null
     */
    public $balance;

    /**
     * @var string
     */
    public $period;

    /**
     * cashApiAggregateGetChartDataDto constructor.
     *
     * @param $period
     * @param $amountIncome
     * @param $amountExpense
     * @param $balance
     */
    public function __construct($period, $amountIncome, $amountExpense, $balance)
    {
        $this->amountIncome = (float) $amountIncome;
        $this->amountExpense = (float) $amountExpense;
        $this->balance = (float) $balance;
        $this->period = $period;
    }
}
