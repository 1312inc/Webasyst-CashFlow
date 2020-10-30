<?php

/**
 * Class cashApiTransactionGetShrinkListResponse
 */
class cashApiTransactionGetShrinkListResponse extends cashApiAbstractResponse
{
    /**
     * cashApiTransactionGetListResponse constructor.
     *
     * @param array|cashApiTransactionResponseDto[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct(200);

        $this->response = [
            'data' => $transactions,
        ];
    }
}
