<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashCategoryUpdateMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiCategoryUpdateResponse
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
        $request = new cashApiCategoryUpdateRequest(
            $this->getApiParamsFetcher()->post('id', true, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->post('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post(
                'type',
                true,
                ApiParamsCaster::CAST_ENUM,
                [cashCategory::TYPE_EXPENSE, cashCategory::TYPE_INCOME]
            ),
            $this->getApiParamsFetcher()->post('color', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->getApiParamsFetcher()->post('sort', true, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->post('is_profit', true, ApiParamsCaster::CAST_BOOLEAN),
            $this->getApiParamsFetcher()->post('parent_category_id', true, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->post('glyph', true, ApiParamsCaster::CAST_STRING_TRIM)
        );

        $category = (new cashApiCategoryUpdateHandler())->handle($request);

        return new cashApiCategoryUpdateResponse($category);
    }
}
