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

    /**
     * @var cashRepeatingTransactionRepository
     */
    private $repeatingTransactionRepository;

    public function __construct()
    {
        $this->userRepository = cash()->getEntityRepository(cashUser::class);
        $this->shopIntegration = new cashShopIntegration();
        $this->repeatingTransactionRepository = cash()->getEntityRepository(cashRepeatingTransaction::class);

    }

    protected function getContactData($contactId): array
    {
        $user = $this->userRepository->getUser($contactId);

        return [
            'name' => $user->getName(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'userpic' => wa()->getConfig()->getHostUrl() . $user->getPhotoUrl(),
        ];
    }

    protected function getRepeatingData($repeatingId): array
    {
        /** @var cashRepeatingTransaction $repeatingTransaction */
        $repeatingTransaction = $this->repeatingTransactionRepository->findById($repeatingId);

        return [
            'interval' => $repeatingTransaction ? $repeatingTransaction->getRepeatingInterval() : null,
            'frequency' => $repeatingTransaction ? (int) $repeatingTransaction->getRepeatingFrequency() : null,
            'occurrences' => $repeatingTransaction ? (int) $repeatingTransaction->getRepeatingOccurrences() : null,
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
