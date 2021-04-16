<?php

/**
 * Class cashCategorySortMethod
 */
class cashCategorySortMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiCategoryCreateResponse
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiCategorySortRequest $request */
        $request = $this->fillRequestWithParams(new cashApiCategorySortRequest());

        (new cashApiCategorySortHandler())->handle($request);

        return new cashApiCategorySortResponse();
    }
}
