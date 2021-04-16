<?php

/**
 * Class cashApiTransactionGetResponse
 */
class cashApiTransactionGetResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionGetResponse constructor.
     *
     * @param cashApiTransactionGetResponseDto $transaction
     */
    public function __construct(cashApiTransactionGetResponseDto $transaction)
    {
        parent::__construct(200);

        $this->response = $transaction;
    }
}
