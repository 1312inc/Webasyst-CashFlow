<?php

/**
 * Class cashTransactionGetListMethod
 */
class cashTransactionGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @throws waAPIException
     */
    public function execute()
    {
        /** @var cashApiTransactionGetListRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetListRequest());

        $this->response = (new cashApiTransactionGetListHandler())->handle($request);
    }
}
