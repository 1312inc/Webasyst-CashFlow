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
     * @var string
     */
    public $currency;

    /**
     * cashApiAggregateGetChartDataDto constructor.
     *
     * @param $period
     * @param $amountIncome
     * @param $amountExpense
     * @param $balance
     * @param $currency
     */
    public function __construct($period, $amountIncome, $amountExpense, $balance, $currency)
    {
        $this->amountIncome = round($amountIncome, 2);
        $this->amountExpense = round($amountExpense, 2);
        $this->balance = round($balance, 2);
        $this->period = $period;
        $this->currency = $currency;
    }
}
