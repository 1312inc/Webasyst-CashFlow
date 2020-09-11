<?php

/**
 * Class cashApiAccountGetListResponse
 */
class cashApiAccountGetListResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAccountGetListResponse constructor.
     *
     * @param array|cashApiAccountResponseDto[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct(200);

        $this->response = $transactions;
    }
}
