<?php

/**
 * Class cashApiAccountUpdateResponse
 */
class cashApiAccountUpdateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAccountUpdateResponse constructor.
     *
     * @param cashApiAccountResponseDto $account
     */
    public function __construct(cashApiAccountResponseDto $account)
    {
        parent::__construct(201);

        $this->response = $account;
    }
}
