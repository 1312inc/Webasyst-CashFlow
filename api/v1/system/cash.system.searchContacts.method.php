<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashSystemSearchContactsMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiSystemSearchContactsResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiSystemSearchContactsRequest(
            $this->fromGet('term', false, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromGet('category_id', false, ApiParamsCaster::CAST_INT),
            $this->fromGet('limit', false, ApiParamsCaster::CAST_INT)
        );

        if (empty($request->getTerm()) && $request->getCategoryId() === null) {
            throw new ApiMissingParamException('term, category_id');
        }

        return new cashApiSystemSearchContactsResponse((new cashApiSystemSearchContactsHandler())->handle($request));
    }
}
