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
    public $start;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $account_id;

    /**
     * @var int
     */
    public $category_id;

    /**
     * @var int
     */
    public $contractor_contact_id;

    /**
     * @var int
     */
    public $create_contact_id;

    /**
     * @var int
     */
    public $import_id;
}
