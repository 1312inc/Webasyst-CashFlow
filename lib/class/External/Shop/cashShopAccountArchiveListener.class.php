<?php

final class cashShopAccountArchiveListener
{
    /**
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function execute(cashEvent $event)
    {
        /** @var cashAccount $account */
        $account = $event->getObject();

        $shopIntegration = new cashShopIntegration();
        if ($shopIntegration->getSettings()->getAccountId() === $account->getId()) {
            $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
            if (!$accounts) {
                throw new kmwaRuntimeException('No active accounts');
            }

            $account = reset($accounts);
            $shopIntegration->getSettings()
                ->setAccountId($account->getId())
                ->setEnabled(false)
                ->save();
            $shopIntegration->turnedOff();
        }
    }
}
