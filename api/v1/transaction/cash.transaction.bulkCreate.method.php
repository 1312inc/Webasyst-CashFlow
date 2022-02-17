<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiCastParamException;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashTransactionBulkCreateMethod extends cashApiNewAbstractMethod
{
    private const MAX_PER_REQUEST = 13;

    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     */
    public function run(): cashApiResponseInterface
    {
        $errors = [];
        $requests = [];
        $responses = [];
        $i = 0;
        foreach ($this->getApiParamsFetcher()->getJsonParams() as $data) {
            if ($i++ >= self::MAX_PER_REQUEST) {
                break;
            }

            try {
                $requests[] = $this->createRequest($data);
            } catch (waException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        try {
            foreach ($requests as $request) {
                if ($request->getTransferAccountId() && $request->getCategoryId() !== cashCategoryFactory::TRANSFER_CATEGORY_ID) {
                    return new cashApiErrorResponse(
                        'Transfer category may not be other than ' . cashCategoryFactory::TRANSFER_CATEGORY_ID
                    );
                }

                if ($request->getCategoryId() == cashCategoryFactory::TRANSFER_CATEGORY_ID && !$request->getTransferAccountId()) {
                    return new cashApiErrorResponse('Missing transfer information');
                }

                $response = (new cashApiTransactionCreateHandler())->handle($request);

                $responses = array_merge($responses, $response);
            }
        } catch (Exception $exception) {
            $errors[] = $exception->getMessage();
            cash()->getLogger()->error('Error on bulk transaction create', $exception);
        }

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($responses))
        );

        return new cashApiTransactionBulkMoveResponse($responses);
    }

    /**
     * @throws ApiException
     * @throws ApiWrongParamException
     * @throws cashValidateException
     * @throws ApiMissingParamException
     * @throws ApiCastParamException
     */
    private function createRequest(array $data): cashApiTransactionCreateRequest
    {
        $external = $this->fromArray($data, 'external', false, ApiParamsCaster::CAST_ARRAY);
        $externalDto = null;
        if ($external) {
            $externalDto = cashApiTransactionCreateExternalDto::fromArray($external);
        }

        $repeatingOnDateStr = $this->fromArray($data, 'repeating_end_ondate');
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

        return new cashApiTransactionCreateRequest(
            $this->fromArray($data, 'amount', true, ApiParamsCaster::CAST_FLOAT),
            $this->fromArray($data, 'date', true, ApiParamsCaster::CAST_DATETIME, 'Y-m-d'),
            $this->fromArray($data, 'account_id', true, ApiParamsCaster::CAST_INT),
            $this->fromArray($data, 'category_id', true, ApiParamsCaster::CAST_INT),
            $this->fromArray($data, 'contractor_contact_id', false, ApiParamsCaster::CAST_INT),
            $this->fromArray($data, 'contractor', false, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromArray($data, 'description', false, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromArray($data, 'is_repeating', false, ApiParamsCaster::CAST_BOOLEAN),
            $this->fromArray($data, 'repeating_frequency', false, ApiParamsCaster::CAST_INT),
            $this->fromArray($data,
                'repeating_interval',
                false,
                ApiParamsCaster::CAST_ENUM,
                array_keys(cashRepeatingTransaction::getRepeatingIntervals())
            ),
            $this->fromArray($data,
                'repeating_end_type',
                false,
                ApiParamsCaster::CAST_ENUM,
                array_keys(cashRepeatingTransaction::getRepeatingEndTypes())
            ),
            $this->fromArray($data, 'repeating_end_after', false, ApiParamsCaster::CAST_INT),
            $repeatingOnDate,
            $this->fromArray($data, 'transfer_account_id', false, ApiParamsCaster::CAST_INT),
            $this->fromArray($data, 'transfer_incoming_amount', false, ApiParamsCaster::CAST_FLOAT),
            $this->fromArray($data, 'is_onbadge', false, ApiParamsCaster::CAST_BOOLEAN),
            $externalDto
        );
    }
}
