<?php

/**
 * Class cashApiAccountCreateResponse
 */
class cashApiAccountCreateResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAccountCreateResponse constructor.
     *
     * @param cashApiAccountResponseDto $account
     */
    public function __construct(cashApiAccountResponseDto $account)
    {
        parent::__construct(201);

        $this->response = $account;
    }
}
