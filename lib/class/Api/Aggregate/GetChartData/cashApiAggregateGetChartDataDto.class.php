<?php

/**
 * Class cashApiAggregateGetChartDataDto
 */
final class cashApiAggregateGetChartDataDto
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
        $this->amountIncome = round($amountIncome, 2);
        $this->amountExpense = round($amountExpense, 2);
        $this->balance = $balance === null ? $balance : round($balance, 2);
        $this->period = $period;
    }
}
