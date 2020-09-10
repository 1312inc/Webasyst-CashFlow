<?php

/**
 * Class cashAccountCreateMethod
 */
class cashAccountCreateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    public function run()
    {
        /** @var cashApiAccountCreateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAccountCreateRequest());

        return (new cashApiAccountCreateHandler())->handle($request);
    }
}
