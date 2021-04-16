<?php

/**
 * Class cashCategoryUpdateMethod
 */
class cashCategoryUpdateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiCategoryUpdateResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiCategoryUpdateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiCategoryUpdateRequest());

        $category = (new cashApiCategoryUpdateHandler())->handle($request);

        return new cashApiCategoryUpdateResponse($category);
    }
}
