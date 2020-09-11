<?php

/**
 * Class cashApiTransactionGetListResponse
 */
class cashApiTransactionGetListResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionGetListResponse constructor.
     *
     * @param array|cashApiTransactionResponseDto[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct(200);

        $this->response = $transactions;
    }
}
