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
            cash()->getModel(cashTransaction::class)->select('count(id)')->limit(30)->fetchField() == 30,
            (int) wa()->getUser()->getId(),
            cash()->getContactRights()->createContactRightsDto(wa()->getUser())
        );
    }
}
