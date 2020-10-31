<?php

final class cashApiAggregateGetBalanceFlowDto
{
    public $currency;

    public $dateFrom;

    public $balanceFrom;

    public $dateTo;

    public $balanceTo;

    public $data;

    /**
     * cashApiAggregateGetBalanceFlowDto constructor.
     *
     * @param $currency
     * @param $dateFrom
     * @param $balanceFrom
     * @param $dateTo
     * @param $balanceTo
     * @param $data
     */
    public function __construct($currency, $dateFrom, $balanceFrom, $dateTo, $balanceTo, array $data)
    {
        $this->currency = $currency;
        $this->dateFrom = $dateFrom;
        $this->balanceFrom = $balanceFrom;
        $this->dateTo = $dateTo;
        $this->balanceTo = $balanceTo;
        $this->data = $data;
    }
}
