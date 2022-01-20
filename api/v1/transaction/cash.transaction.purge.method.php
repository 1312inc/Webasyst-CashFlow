<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

class cashTransactionPurgeMethod extends cashApiNewAbstractMethod
{
    private const MAX_IDS = 100;

    protected $method = [self::METHOD_POST];

    /**
     * @return cashApiResponseInterface
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiTransactionPurgeRequest(
            $this->fromPost('ids', true, ApiParamsCaster::CAST_ARRAY)
        );

        if (count($request->getIds()) > self::MAX_IDS) {
            return new cashApiErrorResponse(
                sprintf_wp('Too many transactions to purge. Max limit is %d', self::MAX_IDS)
            );
        }

        return new cashApiTransactionPurgeResponse((new cashApiTransactionPurgeHandler())->handle($request));
    }
}
