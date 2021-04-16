<?php

/**
 * Class cashApiTransactionGetShrinkListRequest
 */
class cashApiTransactionGetShrinkListRequest
{
    /**
     * @var string
     */
    public $from = '';

    /**
     * @var string
     */
    public $to = '';

    /**
     * @var string Hash to filter transactions, can be "category/X", "account/X", "contractor/X", "currency/XXX"
     */
    public $filter = '';
}
