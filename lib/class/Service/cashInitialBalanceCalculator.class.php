<?php

declare(strict_types=1);

final class cashInitialBalanceCalculator
{
    public function getOnDate(
        cashAggregateChartDataFilterParamsDto $paramsDto,
        DateTimeImmutable $date
    ): array {
        $initialBalanceSql = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->select(['ca.currency currency, sum(ct.amount) balance'])
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'ct.date <= s:from',
                    'account_access' => cash()->getContactRights()
                        ->getSqlForAccountJoinWithFullAccess($paramsDto->contact),
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                    'CASE
                        WHEN ca.is_imaginary = 1 THEN ct.date > NOW()
                        WHEN ca.is_imaginary = -1 THEN NULL
                        ELSE ca.is_imaginary = 0
                    END'
                ]
            )
            ->join(
                [
                    'join cash_account ca on ct.account_id = ca.id',
                ]
            )
            ->addParam('from', $date->format('Y-m-d H:i:s'))
            ->groupBy(['ca.currency']);

        switch (true) {
            case null !== $paramsDto->filter->getAccountId():
                $initialBalanceSql->addAndWhere('ct.account_id = i:account_id')
                    ->addParam('account_id', $paramsDto->filter->getAccountId());

                break;

            case null !== $paramsDto->filter->getCurrency():
                $initialBalanceSql->addAndWhere('ca.currency = s:currency')
                    ->addParam('currency', $paramsDto->filter->getCurrency());

                break;

            case null !== $paramsDto->filter->getCategoryId():
            case null !== $paramsDto->filter->getContractorId():
                $initialBalanceSql->addAndWhere('0');

                break;
        }

        $data = $initialBalanceSql->query()->fetchAll('currency', 1);

        return array_map('floatval', $data);
    }

    public function getOnDateForCurrency(
        cashCurrencyVO $currencyVO,
        DateTimeImmutable $date,
        waContact $contact
    ): ?float {
        $initialBalanceSql = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->select(['sum(ct.amount) balance'])
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'ct.date <= s:from',
                    'account_access' => cash()->getContactRights()->getSqlForAccountJoinWithFullAccess($contact),
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                    'ca.currency = s:currency',
                ]
            )
            ->join(['join cash_account ca on ct.account_id = ca.id'])
            ->addParam('from', $date->format('Y-m-d H:i:s'))
            ->addParam('currency', $currencyVO->getCode());

        $data = $initialBalanceSql->query()->fetchField();

        return (float) $data;
    }

    public function getOnDateForAccount(cashAccount $account, DateTimeImmutable $date, waContact $contact): float
    {
        $initialBalanceSql = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->select(['sum(ct.amount) balance'])
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'ct.date <= s:from',
                    'account_access' => cash()->getContactRights()->getSqlForAccountJoinWithFullAccess($contact),
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                ]
            )
            ->join(['join cash_account ca on ct.account_id = ca.id'])
            ->addParam('from', $date->format('Y-m-d H:i:s'))
            ->addAndWhere('ct.account_id = i:account_id')
            ->addParam('account_id', $account->getId());

        $data = $initialBalanceSql->query()->fetchField();

        return (float) $data;
    }
}