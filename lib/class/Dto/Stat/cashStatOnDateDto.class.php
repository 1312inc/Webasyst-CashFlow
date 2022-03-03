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

    public function __construct(float $income, float $expense, float $summary)
    {
        $this->income = $income;
        $this->expense = (float) abs($expense);
        $this->summary = $summary;
        $this->incomeShorten = cashShorteningService::money($this->income);
        $this->expenseShorten = cashShorteningService::money($this->expense);
        $this->summaryShorten = cashShorteningService::money($this->summary);
    }

    public static function createFromArray(array $data): cashStatOnDateDto
    {
        return new self(
            $data['income'] ?? 0,
            $data['expense'] ?? 0,
            $data['summary'] ?? 0
        );
    }
}
