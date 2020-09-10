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
    public $summary = 0;

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
    public $summaryShorten = '0';

    /**
     * cashAccountStatDto constructor.
     *
     * @param float $income
     * @param float $expense
     * @param float $summary
     */
    public function __construct($income, $expense, $summary)
    {
        $this->income = (float) $income;
        $this->expense = (float) abs($expense);
        $this->summary = (float) $summary;
        $this->incomeShorten = cashShorteningService::money($this->income);
        $this->expenseShorten = cashShorteningService::money($this->expense);
        $this->summaryShorten = cashShorteningService::money($this->summary);
    }
}
