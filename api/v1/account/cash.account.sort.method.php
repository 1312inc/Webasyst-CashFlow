<?php

/**
 * Class cashAccountSortMethod
 */
class cashAccountSortMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAccountSortRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAccountSortRequest());

        (new cashApiAccountSortHandler())->handle($request);

        return new cashApiAccountSortResponse();
    }
}
