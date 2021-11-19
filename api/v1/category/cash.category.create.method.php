<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashCategoryCreateMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiResponseInterface
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
        $request = new cashApiCategoryCreateRequest(
            $this->getApiParamsFetcher()->post('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post(
                'type',
                true,
                ApiParamsCaster::CAST_ENUM,
                [cashCategory::TYPE_EXPENSE, cashCategory::TYPE_INCOME]
            ),
            $this->getApiParamsFetcher()->post('color', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post('sort', false, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->post('is_profit', false, ApiParamsCaster::CAST_BOOLEAN),
            $this->getApiParamsFetcher()->post('parent_category_id', false, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->post('glyph', false, ApiParamsCaster::CAST_STRING_TRIM)
        );

        $response = (new cashApiCategoryCreateHandler())->handle($request);

        return new cashApiCategoryCreateResponse($response);
    }
}
