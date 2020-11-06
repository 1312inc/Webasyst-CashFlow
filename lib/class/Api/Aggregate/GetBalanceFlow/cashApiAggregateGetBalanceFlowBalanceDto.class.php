<?php

final class cashApiAggregateGetBalanceFlowBalanceDto
{
    public $date;

    public $amount;

    public $amountShorten;

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
        $this->amountShorten = cashShorteningService::money($this->amount);
    }
}
