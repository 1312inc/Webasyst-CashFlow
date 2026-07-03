<?php

/**
 * Class cashCompanyUpdateMethod
 */
class cashCompanyUpdateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiResponseInterface
     * @throws cashApiMissingParamException
     * @throws kmwaForbiddenException
     * @throws waException
     */
    function run(): cashApiResponseInterface
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException('Access denied', 403);
        }

        $request = new cashApiCompanyUpdateRequest(
            $this->post('id', true),
            $this->post('name'),
            $this->post('sort'),
        );

        $company = (new cashApiCompanyUpdateHandler())->handle($request);

        return new cashApiCompanyUpdateResponse($company);
    }
}
