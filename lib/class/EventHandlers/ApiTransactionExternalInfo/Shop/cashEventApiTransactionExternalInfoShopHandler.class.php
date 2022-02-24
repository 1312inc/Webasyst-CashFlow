<?php

class cashEventApiTransactionExternalInfoShopHandler implements cashEventApiTransactionExternalInfoHandlerInterface
{
    /**
     * @var cashShopIntegration
     */
    private $shopIntegration;

    public function __construct()
    {
        $this->shopIntegration = new cashShopIntegration();
    }

    public function getResponse(
        cashApiTransactionResponseDto $cashApiTransactionResponseDto
    ): ?cashEventApiTransactionExternalInfoResponseInterface {
        try {
            if ($this->shopIntegration->shopExists()) {
                $data = $cashApiTransactionResponseDto->getData();
                if (isset($data['external_data'])) {
                    $data = json_decode($data['external_data'], true);
                    cash()->getLogger()->debug(print_r($data, 1));
                    $externalEntity = cashTransactionExternalEntityFactory::createFromSource('shop', $data);
                    if ($externalEntity) {
                        $entityId = $externalEntity->getId();
                        $entityIcon = wa()->getConfig()->getHostUrl() . $externalEntity->getAppIcon();
                        $entityUrl = sprintf(
                            '%s%sshop%s',
                            wa()->getConfig()->getHostUrl(),
                            wa('shop')->getConfig()->getBackendUrl(true),
                            $externalEntity->getLink()
                        );
                        $entityName = $externalEntity->getEntityName();
                    }
                }
            }
        } catch (Exception $exception) {
            cash()->getLogger()->error('Shop integration error', $exception);
        }

        return null;
    }

    public function getSource(): string
    {
        return 'shop';
    }
}
