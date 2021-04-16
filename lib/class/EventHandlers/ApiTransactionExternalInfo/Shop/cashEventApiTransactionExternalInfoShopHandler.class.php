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
    ): cashEventApiTransactionExternalInfoResponseInterface {

        $entityName = null;
        $entityUrl = null;
        $entityIcon = null;

        try {
            if ($this->shopIntegration->shopExists()) {
                $data = $cashApiTransactionResponseDto->getData();
                if (isset($data['external_data'])) {
                    $data = json_decode($data['external_data'], true);
                    $externalEntity = cashTransactionExternalEntityFactory::createFromSource('shop', $data);
                    if ($externalEntity) {
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

        return new cashEventApiTransactionExternalInfoResponse(
            '#27bf52',
            'Shop-Script',
            'fas fa-shopping-cart',
            $entityUrl,
            $entityIcon,
            $entityName
        );
    }

    public function getSource(): string
    {
        return 'shop';
    }
}
