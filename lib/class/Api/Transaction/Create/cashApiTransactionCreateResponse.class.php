<?php

/**
 * Class cashApiTransactionCreateResponse
 */
class cashApiTransactionCreateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionCreateResponse constructor.
     *
     * @param array|cashApiTransactionResponseDto[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct(200);

        $this->response = $transactions;
    }
}
