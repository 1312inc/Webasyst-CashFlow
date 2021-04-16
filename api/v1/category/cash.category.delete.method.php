<?php

/**
 * Class cashCategoryDeleteMethod
 */
class cashCategoryDeleteMethod extends cashApiAbstractMethod
{
    protected $method = [self::METHOD_POST, self::METHOD_DELETE];

    /**
     * @return cashApiCategoryDeleteResponse|cashApiErrorResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiCategoryDeleteRequest $request */
        $request = $this->fillRequestWithParams(new cashApiCategoryDeleteRequest());

        if ((new cashApiCategoryDeleteHandler())->handle($request)) {
            return new cashApiCategoryDeleteResponse();
        }

        return new cashApiErrorResponse('Unrecognized error on category delete');
    }
}
