<?php

/**
 * cashScenarioUpdateMethod
 */
class cashScenarioUpdateMethod extends cashApiAbstractMethod
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

        $request = new cashApiScenarioUpdateRequest(
            $this->post('id', true),
            $this->post('name'),
            $this->post('color'),
            $this->post('sort'),
        );

        $scenario = (new cashApiScenarioUpdateHandler())->handle($request);

        return new cashApiScenarioUpdateResponse($scenario);
    }
}
