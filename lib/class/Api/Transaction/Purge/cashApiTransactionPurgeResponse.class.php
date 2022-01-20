<?php

class cashApiTransactionPurgeResponse extends cashApiAbstractResponse
{
    public function __construct(cashApiTransactionPurgeDto $purgeDto)
    {
        parent::__construct(200);

        $this->response = [
            'ok' => $purgeDto->getOk(),
            'fail' => $purgeDto->getFail(),
        ];
    }
}
