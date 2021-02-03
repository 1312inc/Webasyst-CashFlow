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
            return new cashApiErrorResponse('Pass correct transfer category');
        }

        if ($request->category_id == cashCategoryFactory::TRANSFER_CATEGORY_ID && !$request->transfer_account_id) {
            return new cashApiErrorResponse('No transfer information');
        }

        $response = (new cashApiTransactionCreateHandler())->handle($request);

        return new cashApiTransactionCreateResponse($response);
    }
}
