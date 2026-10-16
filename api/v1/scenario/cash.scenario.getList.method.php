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
        if (cash()->getContactRights()->isAdmin(wa()->getUser()) && cashHelper::isPremium()) {
            $scenarios = (new cashApiScenarioGetListHandler())->handle(null);
        } else {
            $scenarios = [];
        }

        return new cashApiScenarioGetListResponse($scenarios);
    }
}
