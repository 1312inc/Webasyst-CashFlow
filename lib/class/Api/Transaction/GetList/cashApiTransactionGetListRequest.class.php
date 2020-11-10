<?php

/**
 * Class cashApiTransactionsGetListRequest
 */
class cashApiTransactionGetListRequest
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
     * @var int
     */
    public $offset;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var null|string Hash to filter transactions, can be "category/X", "account/X", "contractor/X", "currency/XXX"
     */
    public $filter;
}
