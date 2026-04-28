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
            $this->fromPost('accountable_contact_id', false, ApiParamsCaster::CAST_INT),
            $this->fromPost('icon', false, ApiParamsCaster::CAST_STRING_TRIM) ?: null,
            $this->fromPost('is_imaginary', false, ApiParamsCaster::CAST_INT) ?: 0,
            $this->fromPost('description', false, ApiParamsCaster::CAST_STRING_TRIM) ?: ''
        );

        $account = (new cashApiAccountUpdateHandler())->handle($request);

        return new cashApiAccountUpdateResponse($account);
    }
}
