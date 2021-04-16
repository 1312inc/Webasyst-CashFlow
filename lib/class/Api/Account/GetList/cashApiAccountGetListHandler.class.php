<?php

/**
 * Class cashApiAccountGetListHandler
 */
class cashApiAccountGetListHandler implements cashApiHandlerInterface
{
    /**
     * @param $request
     *
     * @return array|cashApiAccountResponseDto[]
     * @throws waException
     */
    public function handle($request): array
    {
        /** @var cashAccountRepository $repository */
        $repository = cash()->getEntityRepository(cashAccount::class);

        $contact = wa()->getUser();
        $accounts = $repository->findAllActiveForContact($contact);

        $accountStats = (new cashCalculationService())->getAccountStatsForDates(
            $contact,
            new DateTime(),
            new DateTime('1970-01-01')
        );

        $response = [];
        foreach ($accounts as $account) {
            $accountResponse = cashApiAccountResponseDto::fromAccount($account);
            $response[] = $accountResponse;
        }

        foreach ($response as $accountResponse) {
            if (isset($accountStats[$accountResponse->id])
                && cash()->getContactRights()->hasFullAccessToAccount($contact, $accountResponse->id)
            ) {
                $accountResponse->stat = (array) $accountStats[$accountResponse->id];
            }
        }

        return $response;
    }
}
