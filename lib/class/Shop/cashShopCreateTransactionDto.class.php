<?php

class cashShopCreateTransactionDto
{
    /**
     * @var cashTransaction
     */
    public $mainTransaction;

    /**
     * @var shopOrder
     */
    public $order;

    /**
     * @var array
     */
    public $params = [];

    /**
     * @var cashTransaction|null
     */
    public $shippingTransaction;

    /**
     * @var cashTransaction|null
     */
    public $taxTransaction;

    /**
     * @var cashTransaction|null
     */
    public $purchaseTransaction;

    /**
     * cashShopCreateTransactionDto constructor.
     *
     * @param array $params
     *
     * @throws waException
     */
    public function __construct($params = [])
    {
        $this->params = $params;
        $this->order = new shopOrder($params['order_id']);
    }
}