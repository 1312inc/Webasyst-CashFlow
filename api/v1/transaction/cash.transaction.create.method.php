<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiCastParamException;

/**
 * @return array of transactions
 * @todo: test & refactor
 *
 * Class cashTransactionCreateMethod
 *
 */
class cashTransactionCreateMethod extends cashApiNewAbstractMethod
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
        $external = $this->fromPost('external', false, ApiParamsCaster::CAST_ARRAY);
        $externalDto = null;
        if ($external) {
            $externalDto = cashApiTransactionCreateExternalDto::fromArray($external);
        }

        $repeatingOnDateStr = $this->fromPost('repeating_end_ondate');
        if (!empty($repeatingOnDateStr)) {
            $repeatingOnDate = DateTimeImmutable::createFromFormat('Y-m-d', $repeatingOnDateStr);

            if ($repeatingOnDate === false) {
                throw new ApiCastParamException(
                    sprintf('Wrong format "%s" for value "%s"', 'Y-m-d', $repeatingOnDateStr)
                );
            }
        } else {
            $repeatingOnDate = null;
        }

        $request = new cashApiTransactionCreateRequest(
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
            $repeatingOnDate,
            $this->fromPost('transfer_account_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('transfer_incoming_amount', false, ApiParamsCaster::CAST_FLOAT),
            $this->fromPost('is_onbadge', false, ApiParamsCaster::CAST_BOOLEAN),
            $externalDto,
            $this->fromPost('is_self_destruct_when_due', false, ApiParamsCaster::CAST_BOOLEAN)

        );

        if ($request->getTransferAccountId() && $request->getCategoryId() !== cashCategoryFactory::TRANSFER_CATEGORY_ID) {
            return new cashApiErrorResponse(
                'Transfer category may not be other than ' . cashCategoryFactory::TRANSFER_CATEGORY_ID
            );
        }

        if ($request->getCategoryId() === cashCategoryFactory::TRANSFER_CATEGORY_ID && !$request->getTransferAccountId()) {
            return new cashApiErrorResponse('Missing transfer information');
        }

        $response = (new cashApiTransactionCreateHandler())->handle($request);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($response))
        );

        return new cashApiTransactionCreateResponse($response);
    }
}
