<?php

/**
 * Class cashShopWelcome
 */
class cashShopWelcome
{
    /**
     * @var cashShopIntegration
     */
    private $shopIntegration;

    /**
     * cashShopWelcome constructor.
     *
     * @param cashShopIntegration $shopIntegration
     */
    public function __construct(cashShopIntegration $shopIntegration)
    {
        $this->shopIntegration = $shopIntegration;
    }

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
        if ($this->shopIntegration->shopExists()) {
            return (int)(new shopOrderModel())->select('count(*)')->where('paid_date is not null')->fetchField();
        }

        return 0;
    }
}
