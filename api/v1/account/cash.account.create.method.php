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
            $this->fromPost('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('currency', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('icon', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('icon_link', false, ApiParamsCaster::CAST_STRING_TRIM) ?: '',
            $this->fromPost('description', false, ApiParamsCaster::CAST_STRING_TRIM) ?: ''
        );

        $response = (new cashApiAccountCreateHandler())->handle($request);

        return new cashApiAccountCreateResponse($response);
    }
}
