<?php

/**
 * Class cashTransactionUpdateMethod
 *
 * @return array of transactions
 */
class cashTransactionUpdateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountUpdateResponse
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionUpdateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionUpdateRequest());

        $response = (new cashApiTransactionUpdateHandler())->handle($request);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator([$response]))
        );

        return new cashApiTransactionUpdateResponse($response);
    }
}
