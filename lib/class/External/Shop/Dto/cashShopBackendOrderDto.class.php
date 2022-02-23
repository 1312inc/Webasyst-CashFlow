<?php

class cashShopBackendOrderDto
{
    /**
     * @var array
     */
    public $income;

    /**
     * @var int
     */
    public $incomeCount;

    /**
     * @var array
     */
    public $expense;

    /**
     * @var int
     */
    public $expenseCount;

    /**
     * @var array
     */
    public $profit;

    /**
     * @var int
     */
    public $profitCount;

    /**
     * @var array
     */
    public $delta;

    /**
     * @var string
     */
    public $link;

    public function __construct(
        array $income,
        int $incomeCount,
        array $expense,
        int $expenseCount,
        array $profit,
        string $profitCount,
        string $link
    ) {
        $this->income = $income;
        array_walk($this->income, static function (&$value, $currency) {
            $value = sprintf('+ %s %s', abs($value), cashCurrencyVO::fromWaCurrency($currency)->getSign());
        });
        $this->incomeCount = $incomeCount;

        $this->expense = $expense;
        array_walk($this->expense, static function (&$value, $currency) {
            $value = sprintf('&minus; %s %s', abs($value), cashCurrencyVO::fromWaCurrency($currency)->getSign());
        });
        $this->expenseCount = $expenseCount;

        $this->profit = $profit;
        array_walk($this->profit, static function (&$value, $currency) {
            $value = sprintf('&minus; %s %s', abs($value), cashCurrencyVO::fromWaCurrency($currency)->getSign());
        });
        $this->profitCount = $profitCount;

        $this->link = $link;

        $allCurrencies = array_merge(array_keys($income), array_keys($expense), array_keys($profit));

        $this->delta = [];
        foreach ($allCurrencies as $currency) {
            $deltaInc = $income[$currency] ?? 0;
            $deltaExp = $expense[$currency] ?? 0;
            $deltaProf = $profit[$currency] ?? 0;
            $delta = $deltaInc
                + ($deltaExp > 0 ? -$deltaExp : $deltaExp)
                + ($deltaProf > 0 ? -$deltaProf : $deltaProf);

            if ($delta) {
                $this->delta[$currency] = sprintf(
                    '%s %s %s',
                    $delta > 0.0 ? '+' : '&minus;',
                    abs($delta),
                    cashCurrencyVO::fromWaCurrency($currency)->getSign()
                );
            }
        }
    }

    /**
     * @param array<cashTransaction> $transactions
     *
     * @return static
     */
    public static function createFromTransactions(array $transactions, string $link): self
    {
        $params = [
            'income' => [],
            'incomeCount' => 0,
            'profit' => [],
            'profitCount' => 0,
            'expense' => [],
            'expenseCount' => 0,
        ];

        foreach ($transactions as $transaction) {
            if ($transaction->getCategory()->isTransfer()) {
                continue;
            }

            $currency = $transaction->getAccount()
                ->getCurrency();

            if ($transaction->getCategory()->getIsProfit()) {
                $type = 'profit';
            } else {
                $type = $transaction->getCategory()->getType();
            }

            if (!isset($params[$type][$currency])) {
                $params[$type][$currency] = 0.0;
            }

            $params[$type][$currency] += $transaction->getAmount();
            $params[$type . 'Count']++;
        }

        return new self(
            $params['income'],
            $params['incomeCount'],
            $params['expense'],
            $params['expenseCount'],
            $params['profit'],
            $params['profitCount'],
            $link
        );
    }
}
