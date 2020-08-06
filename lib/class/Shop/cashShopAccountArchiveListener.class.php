<?php

/**
 * Class cashShopAccountArchiveListener
 */
class cashShopAccountArchiveListener
{
    /**
     * @param cashEvent $event
     *
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function execute(cashEvent $event)
    {
        /** @var cashAccount $account */
        $account = $event->getObject();

        $shopIntegration = new cashShopIntegration();
        if ($shopIntegration->getSettings()->getAccountId() === $account->getId()) {
            $shopIntegration->turnedOff();
        }
    }
}
