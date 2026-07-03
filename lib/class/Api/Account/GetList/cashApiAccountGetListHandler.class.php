<?php

/**
 * Class cashApiAccountGetListHandler
 */
class cashApiAccountGetListHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountGetListRequest $request
     * @return array
     * @throws waException
     */
    public function handle($request): array
    {
        $contact = wa()->getUser();
        if ($request->getCompanyId() && !$contact->isAdmin()) {
            return [];
        }

        /** @var cashAccountRepository $repository */
        $repository = cash()->getEntityRepository(cashAccount::class);
        $accounts = $repository->findAllActiveForContact($contact);

        $accountStats = (new cashCalculationService())->getAccountStatsForDates(
            $contact,
            new DateTime(),
            new DateTime('1970-01-01')
        );

        $response = [];
        foreach ($accounts as $account) {
            if ($request->getCompanyId() && $request->getCompanyId() != $account->getCompanyId()) {
                continue;
            }
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
