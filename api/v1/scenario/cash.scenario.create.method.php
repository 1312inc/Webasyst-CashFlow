<?php

/**
 * cashScenarioCreateMethod
 */
class cashScenarioCreateMethod extends cashApiAbstractMethod
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

        $request = new cashApiScenarioCreateRequest(
            $this->post('name', true),
            $this->post('color'),
            $this->post('sort')
        );
        $scenario = (new cashApiScenarioCreateHandler())->handle($request);

        return new cashApiScenarioCreateResponse($scenario);
    }
}
