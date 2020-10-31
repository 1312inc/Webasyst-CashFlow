<?php

/**
 * Class cashApiAggregateGetBreakDownDto
 */
final class cashApiAggregateGetBreakDownDto
{
    /**
     * @var float
     */
    public $amount;

    /**
     * @var float|null
     */
    public $balance;

    /**
     * @var string
     */
    public $direction;

    /**
     * @var array
     */
    public  $info = [];

    /**
     * @var string
     */
    public $currency;

    /**
     * cashApiAggregateGetBreakDownDto constructor.
     *
     * @param $type
     * @param $amount
     * @param $balance
     * @param $currency
     * @param $info
     */
    public function __construct($type, $amount, $balance, $currency, $info)
    {
        $this->direction = $type;
        $this->amount = round($amount, 2);
        $this->balance = round($balance, 2);
        $this->info = $info;
        $this->currency = $currency;
    }
}
