<?php

/**
 * Class cashAccountDeleteMethod
 */
class cashAccountDeleteMethod extends cashApiAbstractMethod
{
    protected $method = [self::METHOD_POST, self::METHOD_DELETE];

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

        return new cashApiErrorResponse('Unrecognized error on account delete');
    }
}
