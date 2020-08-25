<?php

/**
 * Class cashAccountDtoAssembler
 */
class cashAccountDtoAssembler
{
    /**
     * @param array         $accounts
     * @param waContact     $contact
     * @param DateTime      $endDate
     * @param DateTime $startDate
     *
     * @return cashAccountDto[]
     * @throws waException
     */
    public static function createFromEntitiesWithStat(
        array $accounts,
        waContact $contact,
        DateTime $endDate,
        DateTime $startDate
    ): array {
        $accountDtos = cashDtoFromEntityFactory::fromEntities(cashAccountDto::class, $accounts);

        $accountStats = (new cashCalculationService())->getAccountStatsForDates($contact, $endDate, $startDate);
        /** @var cashAccountDto $accountDto */
        foreach ($accountDtos as $accountDto) {
            if (isset($accountStats[$accountDto->id])
                && cash()->getContactRights()->hasFullAccessToAccount($contact, $accountDto->id)
            ) {
                $accountDto->stat = $accountStats[$accountDto->id];
            }
        }

        return $accountDtos;
    }
}
