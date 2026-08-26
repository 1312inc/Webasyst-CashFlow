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
        $request = new cashApiAccountGetListRequest(
            $this->get('company_id')
        );
        $response = (new cashApiAccountGetListHandler())->handle($request);

        return new cashApiAccountGetListResponse($response);
    }
}
