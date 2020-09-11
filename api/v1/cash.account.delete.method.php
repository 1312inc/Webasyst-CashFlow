<?php

/**
 * Class cashAccountDeleteMethod
 */
class cashAccountDeleteMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountDeleteResponse|cashApiErrorResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAccountDeleteRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAccountDeleteRequest());

        if ((new cashApiAccountDeleteHandler())->handle($request)) {
            return new cashApiAccountDeleteResponse();
        }

        return new cashApiErrorResponse('Some error on account delete');
    }
}
