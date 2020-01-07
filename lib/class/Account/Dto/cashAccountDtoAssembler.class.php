<?php

/**
 * Class cashAccountDtoAssembler
 */
class cashAccountDtoAssembler
{
    /**
     * @param array         $accounts
     * @param DateTime      $endDate
     * @param DateTime|null $startDate
     *
     * @return cashAccountDto[]
     * @throws waException
     */
    public static function createFromEntitiesWithStat(array $accounts, DateTime $endDate, DateTime $startDate = null)
    {
        $accountDtos = cashDtoFromEntityFactory::fromEntities(cashAccountDto::class, $accounts);

        $accountStats = (new cashCalculationService())->getAccountStatsForDates($endDate, $startDate);
        /** @var cashAccountDto $accountDto */
        foreach ($accountDtos as $accountDto) {
            if (isset($accountStats[$accountDto->id])) {
                $accountDto->stat = $accountStats[$accountDto->id];
            }
        }

        return $accountDtos;
    }
}
