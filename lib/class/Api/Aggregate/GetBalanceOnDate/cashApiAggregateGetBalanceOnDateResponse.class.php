<?php

final class cashApiAggregateGetBalanceOnDateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAggregateGetBalanceOnDateResponse constructor.
     *
     * @param array $balance
     */
    public function __construct(array $balance)
    {
        parent::__construct(200);

        foreach ($balance as $currency => $value) {
            $this->response[] = [
                'currency' => $currency,
                'amount' => $value,
                'amountShorten' => cashShorteningService::money($value),
            ];
        }
    }
}
