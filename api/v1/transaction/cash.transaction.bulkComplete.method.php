<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashTransactionBulkCompleteMethod extends cashApiNewAbstractMethod
{
    private const MAX_IDS = 500;

    protected $method = [self::METHOD_POST];

    /**
     * @return cashApiResponseInterface
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws cashValidateException
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiTransactionBulkCompleteRequest(
            $this->fromPost('ids', true, ApiParamsCaster::CAST_ARRAY)
        );

        $result = (new cashApiTransactionBulkCompleteHandler())->handle($request);

        if ($result) {
            return new cashApiResponse();
        }

        return new cashApiErrorResponse('Unrecognized error occured. Browse logs for details.');
    }
}
