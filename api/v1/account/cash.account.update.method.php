<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashAccountUpdateMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountUpdateResponse
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
        $request = new cashApiAccountUpdateRequest(
            $this->fromPost('id', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('currency', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('icon', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('icon_link', false, ApiParamsCaster::CAST_STRING_TRIM) ?: '',
            $this->fromPost('description', true, ApiParamsCaster::CAST_STRING_TRIM) ?: ''
        );

        $account = (new cashApiAccountUpdateHandler())->handle($request);

        return new cashApiAccountUpdateResponse($account);
    }
}
