<?php

/**
 * Class cashAccountUpdateMethod
 */
class cashAccountUpdateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    public function run()
    {
        /** @var cashApiAccountCreateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAccountUpdateRequest());

        return (new cashApiAccountUpdateHandler())->handle($request);
    }
}
