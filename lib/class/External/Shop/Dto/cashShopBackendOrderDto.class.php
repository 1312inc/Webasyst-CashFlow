<?php

final class cashShopBackendOrderDto
{
    /**
     * @var array
     */
    public $income_transactions;

    /**
     * @var array
     */
    public $expense_transactions;

    /**
     * @var array
     */
    public $profit_transactions;

    /**
     * @var array
     */
    public $delta = [];

    /**
     * @var string
     */
    public $link;

    public function __construct(
        array $income_transactions,
        array $expense_transactions,
        array $profit_transactions,
        array $delta,
        string $link
    ) {
        $this->income_transactions = $income_transactions;
        $this->expense_transactions = $expense_transactions;
        $this->profit_transactions = $profit_transactions;
        $this->delta = $delta;
        $this->link = $link;
    }

    /**
     * @param array $transactions
     * @param string $link
     * @return static
     */
    public static function createFromTransactions(array $transactions, string $link): self
    {
        $params = [
            'income' => [],
            'expense' => [],
            'profit' => [],
            'delta' => []
        ];

        foreach ($transactions as $transaction) {
            $transaction['currency'] = cashCurrencyVO::fromWaCurrency($transaction['currency'])->getSign();
            if ($transaction['is_profit']) {
                $params['profit'][] = $transaction;
            } else {
                $params[$transaction['type']][] = $transaction;
            }
            if (!isset($params['delta'][$transaction['currency']])) {
                $params['delta'][$transaction['currency']] = [0.0, 0.0];
            }
            $params['delta'][$transaction['currency']][$transaction['upcoming']] += $transaction['amount'];
        }

        return new self(
            $params['income'],
            $params['expense'],
            $params['profit'],
            $params['delta'],
            $link
        );
    }
}
