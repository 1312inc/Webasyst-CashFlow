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
     * @var float
     */
    public $amountProfit;

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
     * @param $amountProfit
     * @param $balance
     */
    public function __construct($period, $amountIncome, $amountExpense, $amountProfit, $balance)
    {
        $this->amountIncome = $amountIncome === null ? null : round(abs($amountIncome), 2);
        $this->amountExpense = $amountExpense === null ? null : round(abs($amountExpense), 2);
        $this->amountProfit = $amountProfit === null ? null : round(abs($amountProfit), 2);
        $this->balance = $balance === null ? null : round($balance, 2);
        $this->period = $period;
    }
}
