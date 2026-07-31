<?php

/**
 * Class cashCompanyGetListMethod
 */
class cashCompanyGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    function run(): cashApiResponseInterface
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException('Access denied', 403);
        }

        $companies = (new cashApiCompanyGetListHandler())->handle(null);

        return new cashApiCompanyGetListResponse($companies);
    }
}
