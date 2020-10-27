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
     * @param int                                   $total
     * @param int                                   $offset
     * @param int                                   $limit
     */
    public function __construct(array $transactions, $total, $offset, $limit)
    {
        parent::__construct(200);

        $this->response = [
            'offset' => $offset,
            'limit' => $limit,
            'total' => $total,
            'data' => $transactions,
        ];
    }
}
