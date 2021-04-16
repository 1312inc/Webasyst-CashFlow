<?php

/**
 * Class cashApiTransactionResponseDtoAbstractAssembler
 */
abstract class cashApiTransactionResponseDtoAbstractAssembler
{
    /**
     * @var cashUserRepository
     */
    private $userRepository;

    /**
     * @var cashShopIntegration
     */
    private $shopIntegration;

    public function __construct()
    {
        $this->userRepository = new cashUserRepository();
        $this->shopIntegration = new cashShopIntegration();
    }

    protected function getContactData($contactId): array
    {
        $user = $this->userRepository->getUser($contactId);

        return [
            'name' => $user->getName(),
            'firstname' => $user->getContact()->get('firstname'),
            'lastname' => $user->getContact()->get('lastname'),
            'userpic' => wa()->getConfig()->getHostUrl() . $user->getPhotoUrl(),
        ];
    }

    protected function getShopData(array $data): array
    {
        try {
            if (!$this->shopIntegration->shopExists()) {
                return [];
            }

            $externalEntity = cashTransactionExternalEntityFactory::createFromSource('shop', $data);
            if (!$externalEntity) {
                return [];
            }

            return [
                'icon' => wa()->getConfig()->getHostUrl() . $externalEntity->getAppIcon(),
                'link' => sprintf(
                    '%s%s%s%s',
                    wa()->getConfig()->getHostUrl(),
                    rtrim(wa('shop')->getConfig()->getBackendUrl(true), '/'),
                    rtrim(wa()->getAppUrl('shop'), '/'),
                    $externalEntity->getLink()
                ),
                'name' => $externalEntity->getEntityName(),
            ];
        } catch (Exception $exception) {
            cash()->getLogger()->error('Shop integration error', $exception);
        }

        return [];
    }
}
