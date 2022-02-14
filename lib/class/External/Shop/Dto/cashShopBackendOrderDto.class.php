<?php

class cashShopBackendOrderDto
{
    /**
     * @var float
     */
    public $income;

    /**
     * @var int
     */
    public $incomeCount;

    /**
     * @var float
     */
    public $expense;

    /**
     * @var int
     */
    public $expenseCount;

    /**
     * @var float
     */
    public $profit;

    /**
     * @var int
     */
    public $profitCount;

    /**
     * @var float
     */
    public $delta;

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $currency;

    public function __construct(
        float $income,
        int $incomeCount,
        float $expense,
        int $expenseCount,
        float $profit,
        string $profitCount,
        string $link,
        string $currency
    ) {
        $this->income = $income;
        $this->incomeCount = $incomeCount;
        $this->expense = $expense;
        $this->expenseCount = $expenseCount;
        $this->profit = $profit;
        $this->profitCount = $profitCount;

        $this->link = $link;
        $this->currency = $currency;

        $this->delta = $this->income
            + ($this->expense > 0 ? -$this->expense : $this->expense)
            + ($this->profit > 0 ? -$this->profit : $this->profit);
    }

    /**
     * @param array<cashTransaction> $transactions
     *
     * @return static
     */
    public static function createFromTransactions(array $transactions, string $link): self
    {
        $params = [
            'income' => 0.0,
            'incomeCount' => 0,
            'profit' => 0.0,
            'profitCount' => 0,
            'expense' => 0.0,
            'expenseCount' => 0,
        ];

        $firstTransaction = reset($transactions);
        $currency = $firstTransaction->getAccount()
            ->getCurrency();

        $currency = cashCurrencyVO::fromWaCurrency($currency);

        foreach ($transactions as $transaction) {
            if ($transaction->getCategory()->isTransfer()) {
                continue;
            }

            if ($transaction->getCategory()->getIsProfit()) {
                $type = 'profit';
            } else {
                $type = $transaction->getCategory()->getType();
            }

            $params[$type] += $transaction->getAmount();
            $params[$type . 'Count']++;
        }

        return new self(
            $params['income'],
            $params['incomeCount'],
            $params['expense'],
            $params['expenseCount'],
            $params['profit'],
            $params['profitCount'],
            $link,
            $currency->getSign()
        );
    }
}
