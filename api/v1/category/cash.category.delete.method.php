<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashCategoryDeleteMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiCategoryDeleteResponse|cashApiErrorResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiCategoryDeleteRequest(
            $this->getApiParamsFetcher()->post('id', true, ApiParamsCaster::CAST_INT)
        );

        if ((new cashApiCategoryDeleteHandler())->handle($request)) {
            return new cashApiCategoryDeleteResponse();
        }

        return new cashApiErrorResponse('Unrecognized error on category delete');
    }
}
