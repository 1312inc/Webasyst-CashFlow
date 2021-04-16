<?php

/**
 * Class cashApiTransactionsGetListRequest
 */
class cashApiTransactionGetListRequest
{
    /**
     * @var string|DateTimeImmutable
     */
    public $from;

    /**
     * @var string|DateTimeImmutable
     */
    public $to;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var null|string Hash to filter transactions, can be "category/X", "account/X", "contractor/X", "currency/XXX", "import/X", "search/XXXX"
     */
    public $filter;
}
