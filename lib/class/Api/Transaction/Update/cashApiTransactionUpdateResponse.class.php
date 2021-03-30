<?php

/**
 * Class cashApiTransactionUpdateResponse
 */
class cashApiTransactionUpdateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionUpdateResponse constructor.
     *
     * @param array|cashApiTransactionResponseDto $transaction
     */
    public function __construct($transaction)
    {
        parent::__construct(200);

        $this->response = $transaction;
    }
}
