<?php

/**
 * cashScenarioGetListMethod
 */
class cashScenarioGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiResponseInterface
     * @throws kmwaForbiddenException
     * @throws waException
     */
    function run(): cashApiResponseInterface
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException('Access denied', 403);
        }

        $scenarios = (new cashApiScenarioGetListHandler())->handle(null);

        return new cashApiScenarioGetListResponse($scenarios);
    }
}
