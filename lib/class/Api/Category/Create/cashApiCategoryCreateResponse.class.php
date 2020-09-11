<?php

/**
 * Class cashApiCategoryCreateResponse
 */
class cashApiCategoryCreateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryCreateResponse constructor.
     *
     * @param cashApiCategoryResponseDto $account
     */
    public function __construct(cashApiCategoryResponseDto $account)
    {
        parent::__construct(201);

        $this->response = $account;
    }
}
