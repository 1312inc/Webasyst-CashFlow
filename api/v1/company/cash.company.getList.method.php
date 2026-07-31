<?php

/**
 * Class cashCompanyGetListMethod
 */
class cashCompanyGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    function run(): cashApiResponseInterface
    {
        if (cash()->getContactRights()->isAdmin(wa()->getUser()) && cashHelper::isPremium()) {
            $companies = (new cashApiCompanyGetListHandler())->handle(null);
        } else {
            $companies = [];
        }

        return new cashApiCompanyGetListResponse($companies);
    }
}
