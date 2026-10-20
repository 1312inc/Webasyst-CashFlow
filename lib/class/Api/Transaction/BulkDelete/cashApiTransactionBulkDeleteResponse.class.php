<?php

/**
 * Class cashApiTransactionBulkDeleteResponse
 */
class cashApiTransactionBulkDeleteResponse extends cashApiAbstractResponse
{
    public function __construct(array $deleted_ids)
    {
        parent::__construct(200);

        $this->response = $deleted_ids;
    }
}
