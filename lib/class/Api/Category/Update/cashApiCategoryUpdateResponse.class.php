<?php

/**
 * Class cashApiCategoryUpdateResponse
 */
class cashApiCategoryUpdateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiCategoryUpdateResponse constructor.
     *
     * @param cashApiCategoryResponseDto $account
     */
    public function __construct(cashApiCategoryResponseDto $account)
    {
        parent::__construct(200);

        $this->response = $account;
    }
}
