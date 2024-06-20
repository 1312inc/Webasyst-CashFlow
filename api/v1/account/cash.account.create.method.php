<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashAccountCreateMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws cashValidateException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiAccountCreateRequest(
            $this->fromPost('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('currency', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('icon', false, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('is_imaginary', true, ApiParamsCaster::CAST_INT) ?: 0,
            $this->fromPost('description', false, ApiParamsCaster::CAST_STRING_TRIM) ?: ''
        );

        $response = (new cashApiAccountCreateHandler())->handle($request);

        return new cashApiAccountCreateResponse($response);
    }
}
