<?php

/**
 * Class cashCompanyCreateMethod
 */
class cashCompanyCreateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiResponseInterface
     * @throws cashApiMissingParamException
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    function run(): cashApiResponseInterface
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException('Access denied', 403);
        }

        $request = new cashApiCompanyCreateRequest(
            $this->post('name', true),
            $this->post('sort')
        );

        $company = (new cashApiCompanyCreateHandler())->handle($request);

        return new cashApiCompanyCreateResponse($company);
    }
}
