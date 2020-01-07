<?php

/**
 * Class cashCalculationService
 */
final class cashCalculationService
{
    /**
     * @param DateTime      $endDate
     * @param DateTime|null $startDate
     *
     * @return cashStatAccountDto[]
     * @throws waException
     * @todo: cache?
     */
    public function getAccountStatsForDates(DateTime $endDate, DateTime $startDate = null)
    {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);

        $startDate = ifset($startDate, new DateTime('1970-01-01'));
        $data = $model->getStatDataForAccounts($startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d H:i:s'));

        $dtos = [];
        foreach ($data as $datum) {
            /** @var cashStatAccountDto $dto */
            $dtos[$datum['id']] = new cashStatAccountDto(
                $datum['id'],
                $datum['income'],
                $datum['expense'],
                $datum['summary']
            );
        }

        return $dtos;
    }

    /**
     * @param DateTime         $onDate
     * @param cashAccount|null $account
     *
     * @return cashStatAccountDto[]
     * @throws waException
     */
    public function getOnHandOnDate(DateTime $onDate, cashAccount $account)
    {
        /** @var cashAccountModel $model */
        $model = cash()->getModel(cashAccount::class);

        $accounts = [];
        if ($account->getId()) {
            $accounts[] = $account->getId();
        }

        $data = $model->getStatDataForAccounts('1970-01-01 00:00:00', $onDate->format('Y-m-d H:i:s'), $accounts);

        $summaryData = [];
        foreach ($data as $datum) {
            if (!isset($summaryData[$datum['currency']])) {
                $summaryData[$datum['currency']] = [
                    'summary' => 0,
                    'income' => 0,
                    'expense' => 0,
                    'currency' => cashCurrencyVO::fromWaCurrency($datum['currency']),
                ];
            }
            $summaryData[$datum['currency']]['summary'] += $datum['summary'];
            $summaryData[$datum['currency']]['income'] += $datum['income'];
            $summaryData[$datum['currency']]['expense'] += $datum['expense'];
        }

        $summary = [];
        foreach ($summaryData as $currency => $summaryDatum) {
            if ($summaryDatum['summary'] > 0) {
                $summary[$currency] = new cashStatOnHandDto(
                    cashCurrencyVO::fromWaCurrency($currency),
                    new cashStatAccountDto(
                        0,
                        $summaryDatum['income'],
                        $summaryDatum['expense'],
                        $summaryDatum['summary']
                    )
                );
            }
        }

        return $summary;
    }
}
