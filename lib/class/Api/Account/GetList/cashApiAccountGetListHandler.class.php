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
        $company_id = ($request ? $request->getCompanyId() : 0);
        if ($company_id && !$contact->isAdmin()) {
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
            if ($company_id && $company_id != $account->getCompanyId()) {
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
