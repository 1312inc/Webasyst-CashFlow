<?php

final class cashApiAggregateGetBalanceFlowBalanceDto
{
    public $date;

    public $amount;

    /**
     * cashApiAggregateGetBalanceFlowBalanceDto constructor.
     *
     * @param $date
     * @param $amount
     */
    public function __construct($date, $amount)
    {
        $this->date = $date;
        $this->amount = round($amount, 2);
    }
}
