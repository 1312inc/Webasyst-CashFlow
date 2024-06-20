<?php

final class cashShopBackendOrderDto
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
    public $delta = [];

    /**
     * @var array
     */
    public $income_upcoming;

    /**
     * @var int
     */
    public $upcoming_income_count;

    /**
     * @var array
     */
    public $expense_upcoming;

    /**
     * @var int
     */
    public $upcoming_expense_count;

    /**
     * @var array
     */
    public $upcoming_profit;

    /**
     * @var int
     */
    public $upcoming_profit_count;
    /**
     * @var array
     */
    public $upcoming_delta = [];

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
        array $income_upcoming,
        int $upcoming_income_count,
        array $expense_upcoming,
        int $upcoming_expense_count,
        array $upcoming_profit,
        int $upcoming_profit_count,
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

        $this->income_upcoming = $income_upcoming;
        array_walk($this->income_upcoming, static function (&$value, $currency) {
            $value = sprintf('+ %s %s', abs($value), cashCurrencyVO::fromWaCurrency($currency)->getSign());
        });
        $this->upcoming_income_count = $upcoming_income_count;

        $this->expense_upcoming = $expense_upcoming;
        array_walk($this->expense_upcoming, static function (&$value, $currency) {
            $value = sprintf('&minus; %s %s', abs($value), cashCurrencyVO::fromWaCurrency($currency)->getSign());
        });
        $this->upcoming_expense_count = $upcoming_expense_count;

        $this->upcoming_profit = $upcoming_profit;
        array_walk($this->upcoming_profit, static function (&$value, $currency) {
            $value = sprintf('&minus; %s %s', abs($value), cashCurrencyVO::fromWaCurrency($currency)->getSign());
        });
        $this->upcoming_profit_count = $upcoming_profit_count;

        $this->link = $link;

        $allCurrencies = array_merge(
            array_keys($income),
            array_keys($expense),
            array_keys($profit),
            array_keys($income_upcoming),
            array_keys($expense_upcoming),
            array_keys($upcoming_profit)
        );

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

            $upcoming_delta_inc = $income_upcoming[$currency] ?? 0;
            $upcoming_delta_exp = $expense_upcoming[$currency] ?? 0;
            $upcoming_delta_prof = $upcoming_profit[$currency] ?? 0;
            $upcoming_delta = $upcoming_delta_inc
                + ($upcoming_delta_exp > 0 ? -1 : 1) * $upcoming_delta_exp
                + ($upcoming_delta_prof > 0 ? -1 : 1) * $upcoming_delta_prof;
            if ($upcoming_delta) {
                $this->upcoming_delta[$currency] = sprintf(
                    '%s %s %s',
                    $upcoming_delta > 0.0 ? '+' : '&minus;',
                    abs($upcoming_delta),
                    cashCurrencyVO::fromWaCurrency($currency)->getSign()
                );
            }
        }
    }

    /**
     * @param array<cashTransaction> $transactions
     * @param string $link
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
            'incomeUpcoming' => [],
            'incomeUpcomingCount' => 0,
            'expenseUpcoming' => [],
            'expenseUpcomingCount' => 0,
            'profitUpcoming' => [],
            'profitUpcomingCount' => 0
        ];

        foreach ($transactions as $transaction) {
            if ($transaction->getCategory()->isTransfer()) {
                continue;
            }

            $currency = $transaction->getAccount()->getCurrency();

            if ($transaction->getCategory()->getIsProfit()) {
                $type = 'profit';
            } else {
                $type = $transaction->getCategory()->getType();
            }
            if ($transaction->isForecast()) {
                $type .= 'Upcoming';
            }

            if (!isset($params[$type][$currency])) {
                $params[$type][$currency] = 0.0;
            }

            $params[$type][$currency] += $transaction->getAmount();
            $params[$type.'Count']++;
        }

        return new self(
            $params['income'],
            $params['incomeCount'],
            $params['expense'],
            $params['expenseCount'],
            $params['profit'],
            $params['profitCount'],
            $params['incomeUpcoming'],
            $params['incomeUpcomingCount'],
            $params['expenseUpcoming'],
            $params['expenseUpcomingCount'],
            $params['profitUpcoming'],
            $params['profitUpcomingCount'],
            $link
        );
    }
}
