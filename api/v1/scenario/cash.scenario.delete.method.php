<?php

/**
 * cashScenarioDeleteMethod
 */
class cashScenarioDeleteMethod extends cashApiAbstractMethod
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
        if (!cashHelper::isPremium()) {
            return new cashApiErrorResponse('payment_required', 'Payment premium version required', 402);
        } elseif (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException('Access denied', 403);
        }

        $request = new cashApiScenarioDeleteRequest(
            $this->post('id', true),
        );

        (new cashApiScenarioDeleteHandler())->handle($request);

        return new cashApiScenarioDeleteResponse();
    }
}