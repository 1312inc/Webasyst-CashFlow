<?php

/**
 * Class cashTransactionCreateMethod
 *
 * @return array of transactions
 */
class cashTransactionCreateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
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
        /** @var cashApiTransactionCreateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionCreateRequest());
        if ($request->transfer_account_id && $request->category_id !== cashCategoryFactory::TRANSFER_CATEGORY_ID) {
            return new cashApiErrorResponse(
                'Transfer category may not be other than ' . cashCategoryFactory::TRANSFER_CATEGORY_ID
            );
        }

        if ($request->category_id == cashCategoryFactory::TRANSFER_CATEGORY_ID && !$request->transfer_account_id) {
            return new cashApiErrorResponse('Missing transfer information');
        }

        $response = (new cashApiTransactionCreateHandler())->handle($request);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($response))
        );

        return new cashApiTransactionCreateResponse($response);
    }
}
