<?php

/**
 * Class cashApiTransactionDeleteResponse
 */
class cashApiTransactionDeleteResponse extends cashApiAbstractResponse
{
    public function __construct(array $deletedIds)
    {
        parent::__construct(200);

        $this->response = $deletedIds;
    }
}
