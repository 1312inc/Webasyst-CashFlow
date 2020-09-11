<?php

/**
 * Class cashApiCategoryGetListResponse
 */
class cashApiCategoryGetListResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryGetListResponse constructor.
     *
     * @param array|cashApiCategoryResponseDto[] $transactions
     */
    public function __construct(array $transactions)
    {
        parent::__construct(200);

        $this->response = $transactions;
    }
}
