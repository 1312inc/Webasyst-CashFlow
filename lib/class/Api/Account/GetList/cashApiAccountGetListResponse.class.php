<?php

/**
 * Class cashApiAccountGetListResponse
 */
class cashApiAccountGetListResponse extends cashApiAbstractResponse
{
    /**
     * cashApiAccountGetListResponse constructor.
     *
     * @param array|cashApiAccountResponseDto[] $accounts
     */
    public function __construct(array $accounts)
    {
        parent::__construct(200);

        $this->response = $accounts;
    }
}
