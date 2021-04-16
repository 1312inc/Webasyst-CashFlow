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
     * @param array  $balances
     */
    public function __construct(string $currency, array $data, array $balances)
    {
        $this->currency = $currency;
        $this->data = $data;
        $this->balances = $balances;
    }
}
