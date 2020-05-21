<?php

/**
 * Class cashShopWelcome
 */
class cashShopWelcome
{
    /**
     * @param waContact $contact
     *
     * @return bool
     */
    public function welcomePassed(waContact $contact)
    {
        return $contact->getSettings(cashConfig::APP_ID, 'welcome_passed', false) !== false;
    }

    /**
     * @param waContact $contact
     */
    public function setWelcomePassed(waContact $contact)
    {
        $contact->setSettings(cashConfig::APP_ID, 'welcome_passed', date('Y-m-d H:i:s'));
    }

    /**
     * @return int
     * @throws waDbException
     * @throws waException
     */
    public function countOrdersToProcess()
    {
        return (int) (new shopOrderModel())->select('count(*)')->where('paid_date is not null')->fetchField();
    }
}
