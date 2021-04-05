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

        $icon = '';
        $link = '';
        $name = 'Shop-Script';

        try {
            if ($this->shopIntegration->shopExists()) {
                $data = $cashApiTransactionResponseDto->getData();
                if (isset($data['external_data'])) {
                    $data = json_decode($data['external_data'], true);
                    $externalEntity = cashTransactionExternalEntityFactory::createFromSource('shop', $data);
                    if ($externalEntity) {
                        $icon = wa()->getConfig()->getHostUrl() . $externalEntity->getAppIcon();
                        $link = sprintf(
                            '%s%s%s%s',
                            wa()->getConfig()->getHostUrl(),
                            rtrim(wa('shop')->getConfig()->getBackendUrl(true), '/'),
                            rtrim(wa()->getAppUrl('shop'), '/'),
                            $externalEntity->getLink()
                        );
                        $name = $externalEntity->getEntityName();
                    }
                }
            }
        } catch (Exception $exception) {
            cash()->getLogger()->error('Shop integration error', $exception);
        }

        return new cashEventApiTransactionExternalInfoResponse('#27bf52', $name, 'fas fa-shopping-cart', $link, $icon);
    }

    public function getSource(): string
    {
        return 'shop';
    }
}
