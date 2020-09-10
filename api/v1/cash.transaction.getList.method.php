<?php

/**
 * Class cashTransactionGetListMethod
 */
class cashTransactionGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function run()
    {
        /** @var cashApiTransactionGetListRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetListRequest());

        return (new cashApiTransactionGetListHandler())->handle($request);
    }
}
