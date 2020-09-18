<?php

/**
 * Class cashApiTransactionUpdateResponse
 */
class cashApiTransactionUpdateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionUpdateResponse constructor.
     *
     * @param array|cashApiTransactionResponseDto[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct(200);

        $this->response = $transactions;
    }
}
