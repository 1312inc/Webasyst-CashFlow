<?php

final class cashCategoryGetListMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiCategoryGetListResponse
     *
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $response = (new cashApiCategoryGetListHandler())->handle(null);

        return new cashApiCategoryGetListResponse($response);
    }
}
