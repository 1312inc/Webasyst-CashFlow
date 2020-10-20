<?php

/**
 * Class cashApiAggregateGetBreakDownDto
 */
class cashApiAggregateGetBreakDownDto
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
    public $info = [];

    /**
     * cashApiAggregateGetBreakDownDto constructor.
     *
     * @param $type
     * @param $amount
     * @param $balance
     * @param $info
     */
    public function __construct($type, $amount, $balance, $info)
    {
        $this->direction = $type;
        $this->amount = round($amount, 2);
        $this->balance = round($balance, 2);
        $this->info = $info;
    }
}
