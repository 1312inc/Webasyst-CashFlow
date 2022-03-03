<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashContactGetListMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiContactSearchResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException(_w('You can not get contact list'));
        }

        $request = new cashApiContactGetListRequest(
            $this->fromGet('offset', false, ApiParamsCaster::CAST_INT) ?? 0,
            $this->fromGet('limit', false, ApiParamsCaster::CAST_INT) ?? cashApiContactGetListRequest::MAX_LIMIT
        );

        return new cashApiContactGetListResponse((new cashApiContactGetListHandler())->handle($request));
    }
}
