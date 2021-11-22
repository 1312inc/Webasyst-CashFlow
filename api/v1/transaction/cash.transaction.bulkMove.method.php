<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashTransactionBulkMoveMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiTransactionBulkMoveResponse|cashApiErrorResponse
     *
     * @todo: omg
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws ReflectionException
     * @throws cashValidateException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws kmwaRuntimeException
     * @throws waDbException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiTransactionBulkMoveRequest(
            $this->fromPost('ids', true, ApiParamsCaster::CAST_ARRAY),
            $this->fromPost('category_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('account_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('contractor_contact_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('contractor_contact', false, ApiParamsCaster::CAST_STRING_TRIM)
        );

        $transactions = (new cashApiTransactionBulkMoveHandler())->handle($request);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($transactions))
        );

        return new cashApiTransactionBulkMoveResponse($transactions);
    }
}
