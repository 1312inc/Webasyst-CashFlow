<?php

/**
 * Class cashAccountGetListMethod
 */
class cashAccountGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiAccountGetListResponse
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $response = (new cashApiAccountGetListHandler())->handle(null);

        return new cashApiAccountGetListResponse($response);
    }
}
