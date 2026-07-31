<?php

/**
 * Class cashCompanyGetListMethod
 */
class cashCompanyGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    function run(): cashApiResponseInterface
    {
        $companies = (new cashApiCompanyGetListHandler())->handle(null);

        return new cashApiCompanyGetListResponse($companies);
    }
}
