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
     * @var int
     */
    public $countIncome;

    /**
     * @var int
     */
    public $countExpense;

    /**
     * @var int
     */
    public $countProfit;


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
     * @param $data array
     */
    public function __construct($data = [])
    {
        $this->amountIncome = isset($data['incomeAmount']) ? round(abs($data['incomeAmount']), 2) : null;
        $this->amountExpense = isset($data['expenseAmount']) ? round(abs($data['expenseAmount']), 2) : null;
        $this->amountProfit = isset($data['profitAmount']) ? round(abs($data['profitAmount']), 2) : null;
        $this->countIncome = isset($data['countIncome']) ? (int) $data['countIncome'] : null;
        $this->countExpense = isset($data['countExpense']) ? (int) $data['countExpense'] : null;
        $this->countProfit = isset($data['countProfit']) ? (int) $data['countProfit'] : null;
        $this->balance = isset($data['balance']) ? round($data['balance'], 2) : null;
        $this->period = ifset($data, 'groupkey', '');
    }
}
