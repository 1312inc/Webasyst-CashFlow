<?php

/**
 * Class cashCategoryCreateMethod
 */
class cashCategoryCreateMethod extends cashApiAbstractMethod
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
        /** @var cashApiCategoryCreateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiCategoryCreateRequest());

        $response = (new cashApiCategoryCreateHandler())->handle($request);

        return new cashApiCategoryCreateResponse($response);
    }
}
