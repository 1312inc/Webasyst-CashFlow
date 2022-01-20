<?php

class cashApiTransactionRestoreResponse extends cashApiAbstractResponse
{
    public function __construct(cashApiTransactionRestoreDto $restoreDto)
    {
        parent::__construct(200);

        $this->response = [
            'ok' => $restoreDto->getOk(),
            'fail' => $restoreDto->getFail(),
        ];
    }
}
