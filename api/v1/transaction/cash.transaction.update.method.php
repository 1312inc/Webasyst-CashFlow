<?php

use ApiPack1312\ApiParamsCaster;

class cashTransactionUpdateMethod extends cashApiNewAbstractMethod
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
        $external = $this->fromPost('external', false, ApiParamsCaster::CAST_ARRAY);
        $externalDto = null;
        if ($external) {
            $externalDto = cashApiTransactionCreateExternalDto::fromArray($external);
        }

        $request = new cashApiTransactionUpdateRequest(
            $this->fromPost('id', true, ApiParamsCaster::CAST_INT),
            $this->fromPost('apply_to_all_in_future', true, ApiParamsCaster::CAST_BOOLEAN),
            $this->fromPost('amount', true, ApiParamsCaster::CAST_FLOAT),
            $this->fromPost('date', true, ApiParamsCaster::CAST_DATETIME, 'Y-m-d'),
            $this->fromPost('account_id', true, ApiParamsCaster::CAST_INT),
            $this->fromPost('category_id', true, ApiParamsCaster::CAST_INT),
            $this->fromPost('contractor_contact_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('contractor', false, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('description', false, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('is_repeating', false, ApiParamsCaster::CAST_BOOLEAN),
            $this->fromPost('repeating_frequency', false, ApiParamsCaster::CAST_INT),
            $this->fromPost(
                'repeating_interval',
                false,
                ApiParamsCaster::CAST_ENUM,
                array_keys(cashRepeatingTransaction::getRepeatingIntervals())
            ),
            $this->fromPost(
                'repeating_end_type',
                false,
                ApiParamsCaster::CAST_ENUM,
                array_keys(cashRepeatingTransaction::getRepeatingEndTypes())
            ),
            $this->fromPost('repeating_end_after', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('repeating_end_ondate', false, ApiParamsCaster::CAST_DATETIME, 'Y-m-d'),
            $this->fromPost('transfer_account_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('transfer_incoming_amount', false, ApiParamsCaster::CAST_FLOAT),
            $this->fromPost('is_onbadge', false, ApiParamsCaster::CAST_BOOLEAN),
            $externalDto
        );

        $response = (new cashApiTransactionUpdateHandler())->handle($request);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator([$response]))
        );

        return new cashApiTransactionUpdateResponse($response);
    }
}
