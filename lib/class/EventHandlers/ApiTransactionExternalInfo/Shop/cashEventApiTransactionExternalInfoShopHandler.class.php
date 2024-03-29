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
                    $externalData = json_decode($data['external_data'], true);
                    cash()->getLogger()->debug(print_r($data, 1));
                    $externalEntity = cashTransactionExternalEntityFactory::createFromSource(
                        'shop',
                        $externalData,
                        $data['external_hash'] ?? null
                    );
                    if ($externalEntity) {
                        return new cashEventApiTransactionExternalInfoResponse(
                            $externalEntity->getId(),
                            '#27bf52',
                            'Shop-Script',
                            'fas fa-shopping-cart',
                            sprintf(
                                '%s%sshop%s',
                                wa()->getConfig()->getHostUrl(),
                                wa('shop')->getConfig()->getBackendUrl(true),
                                $externalEntity->getLink()
                            ),
                            wa()->getConfig()->getHostUrl() . $externalEntity->getAppIcon(),
                            $externalEntity->getEntityName()
                        );
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
