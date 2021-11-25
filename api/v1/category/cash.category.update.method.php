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
            $this->fromPost('id', true, ApiParamsCaster::CAST_INT),
            $this->fromPost('name', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost(
                'type',
                true,
                ApiParamsCaster::CAST_ENUM,
                [cashCategory::TYPE_EXPENSE, cashCategory::TYPE_INCOME]
            ),
            $this->fromPost('color', true, ApiParamsCaster::CAST_STRING_TRIM),
            $this->fromPost('sort', true, ApiParamsCaster::CAST_INT),
            $this->fromPost('is_profit', true, ApiParamsCaster::CAST_BOOLEAN),
            $this->fromPost('parent_category_id', true, ApiParamsCaster::CAST_INT),
            $this->fromPost('glyph', true, ApiParamsCaster::CAST_STRING_TRIM)
        );

        $category = (new cashApiCategoryUpdateHandler())->handle($request);

        return new cashApiCategoryUpdateResponse($category);
    }
}
