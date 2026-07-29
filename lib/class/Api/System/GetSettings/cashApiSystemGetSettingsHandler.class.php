<?php

/**
 * Class cashApiSystemGetSettingsHandler
 */
class cashApiSystemGetSettingsHandler implements cashApiHandlerInterface
{
    /**
     * @param null $request
     *
     * @return cashSystemSettingsDto
     * @throws waException
     */
    public function handle($request)
    {
        return new cashSystemSettingsDto(
            (int) wa()->getUser()->getId(),
            cashHelper::isPremium(),
            cash()->getContactRights()->createContactRightsDto(wa()->getUser())
        );
    }
}
