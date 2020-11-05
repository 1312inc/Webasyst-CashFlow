<?php

/**
 * Class cashStatOnDateDto
 */
class cashStatOnDateDto
{
    /**
     * @var float
     */
    public $income = 0;

    /**
     * @var float
     */
    public $expense = 0;

    /**
     * @var float
     */
    public $balance = 0;

    /**
     * @var string
     */
    public $incomeShorten = '0';

    /**
     * @var string
     */
    public $expenseShorten = '0';

    /**
     * @var string
     */
    public $balanceShorten = '0';

    /**
     * cashAccountStatDto constructor.
     *
     * @param float $income
     * @param float $expense
     * @param float $balance
     */
    public function __construct($income, $expense, $balance)
    {
        $this->income = (float) $income;
        $this->expense = (float) abs($expense);
        $this->balance = (float) $balance;
        $this->incomeShorten = cashShorteningService::money($this->income);
        $this->expenseShorten = cashShorteningService::money($this->expense);
        $this->balanceShorten = cashShorteningService::money($this->balance);
    }
}
