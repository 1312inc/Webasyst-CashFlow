<?php

/**
 * Class cashTemplateGetListMethod
 */
class cashTemplateGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function run(): cashApiResponseInterface
    {
        if (!cashHelper::isPremium()) {
            return new cashApiErrorResponse('payment_required', 'Payment premium version required', 402);
        }
        if (cash()->getContactRights()->isAdmin(wa()->getUser())) {
            $templates = (new cashApiTemplateGetListHandler())->handle(null);
        } else {
            $templates = [];
        }

        return new cashApiTemplateGetListResponse($templates);
    }
}
