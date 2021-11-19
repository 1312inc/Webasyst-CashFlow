<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

/**
 * Class cashAccountCreateMethod
 */
class cashAccountCreateMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     * @throws  ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiAccountCreateRequest(
            $this->getApiParamsFetcher()->post('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post('currency', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post('icon', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post('icon_link', false, ApiParamsCaster::CAST_STRING_TRIM) ?: '',
            $this->getApiParamsFetcher()->post('description', false, ApiParamsCaster::CAST_STRING_TRIM) ?: ''
        );

        $response = (new cashApiAccountCreateHandler())->handle($request);

        return new cashApiAccountCreateResponse($response);
    }
}
