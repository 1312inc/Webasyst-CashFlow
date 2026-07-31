<?php

/**
 * Class cashCompanyDeleteMethod
 */
class cashCompanyDeleteMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    function run(): cashApiResponseInterface
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException('Access denied', 403);
        }

        $request = new cashApiCompanyDeleteRequest(
            $this->post('id', true),
        );

        (new cashApiCompanyDeleteHandler())->handle($request);

        return new cashApiCompanyDeleteResponse();
    }
}
