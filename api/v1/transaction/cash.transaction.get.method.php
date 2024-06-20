<?php

/**
 * Class cashTransactionGetMethod
 */
class cashTransactionGetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return cashApiTransactionGetResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetRequest());
        $transaction = (new cashApiTransactionGetHandler())->handle($request);

        return new cashApiTransactionGetResponse($transaction);
    }
}
