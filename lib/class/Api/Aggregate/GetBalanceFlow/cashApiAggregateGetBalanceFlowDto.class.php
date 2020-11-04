<?php

final class cashApiAggregateGetBalanceFlowDto
{
    public $currency;

    /**
     * @var cashApiAggregateGetBalanceFlowBalanceDto[]
     */
    public $balances = [];

    public $data;

    /**
     * cashApiAggregateGetBalanceFlowDto constructor.
     *
     * @param string $currency
     * @param array  $data
     */
    public function __construct($currency, array $data)
    {
        $this->currency = $currency;
        $this->data = $data;
    }
}
