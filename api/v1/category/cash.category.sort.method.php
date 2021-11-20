<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashCategorySortMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiCategoryCreateResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiCategorySortRequest(
            $this->fromPost('order', true, ApiParamsCaster::CAST_ARRAY)
        );

        (new cashApiCategorySortHandler())->handle($request);

        return new cashApiCategorySortResponse();
    }
}
