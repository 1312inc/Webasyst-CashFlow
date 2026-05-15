<?php

final class cashReportDdsAccountDataProvider implements cashReportDdsDataProviderInterface
{
    /**
     * @var cashTransactionModel
     */
    private $transactionModel;

    /**
     * @var cashAccountRepository
     */
    private $accountRep;

    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
        $this->accountRep = cash()->getEntityRepository(cashAccount::class);
    }

    /**
     * @return cashReportDdsStatDto[]
     * @throws waException
     */
    public function getDataForPeriod(cashReportPeriod $period): array
    {
        $data = $this->transactionModel->query("
            SELECT ct.account_id account, IF(ct.amount < 0, s:cat_ex, s:cat_in) category_type, ca.currency currency, MONTH(ct.date) month, SUM(ct.amount) per_month, ca.is_imaginary
            FROM cash_transaction ct
            JOIN cash_account ca ON ct.account_id = ca.id
            JOIN cash_category cc ON ct.category_id = cc.id
            WHERE ct.date >= s:start AND ct.date < s:end
                AND ct.category_id <> -1312
                AND ca.is_archived = 0
                AND ct.is_archived = 0
                AND IF (ca.is_imaginary = -1, NULL, true) 
            GROUP BY category_type, ct.account_id, ca.currency, MONTH(ct.date)
        ", [
            'cat_ex' => cashCategory::TYPE_EXPENSE,
            'cat_in' => cashCategory::TYPE_INCOME,
            'start'  => $period->getStart()->format('Y-m-d'),
            'end'    => $period->getEnd()->format('Y-m-d'),
        ])->fetchAll();

        $rawData = [];
        $current_month = (int) date('n');

        foreach ($data as $datum) {
            $month = $datum['month'];
            $currency = $datum['currency'];
            $catType = $datum['category_type'];
            $account = $datum['account'];
            $perMonth = $datum['per_month'];

            if (!isset($rawData[cashCategory::TYPE_INCOME][cashReportDdsService::ALL_INCOME_KEY][$month][$currency])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'account' => $catType,
                        'type' => $catType,
                        'currency' => $currency,
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][cashReportDdsService::ALL_INCOME_KEY][$groupingDto->key][$currency] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][cashReportDdsService::ALL_EXPENSE_KEY][$groupingDto->key][$currency] = $initVals;
                }
                $rawData[cashCategory::TYPE_INCOME][cashReportDdsService::ALL_INCOME_KEY]['total'][$currency]['per_month'] = .0;
                $rawData[cashCategory::TYPE_EXPENSE][cashReportDdsService::ALL_EXPENSE_KEY]['total'][$currency]['per_month'] = .0;
            }

            if (!isset($rawData[$catType][$account][$month][$currency])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $rawData[$catType][$account][$groupingDto->key][$currency] = [
                        'account' => $account,
                        'type' => $catType,
                        'currency' => $currency,
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
                $rawData[$catType][$account]['total'][$currency]['per_month'] = .0;
            }

            if ($catType === cashCategory::TYPE_INCOME) {
                $categoryTypeKey = cashReportDdsService::ALL_INCOME_KEY;
            } else {
                $categoryTypeKey = cashReportDdsService::ALL_EXPENSE_KEY;
            }
            $rawData[$catType][$account][$month][$currency]['per_month'] = (float) $perMonth;
            $rawData[$catType][$account]['total'][$currency]['per_month'] += (float) $perMonth;

            if (
                0 === (int) $datum['is_imaginary']
                || 1 === (int) $datum['is_imaginary'] && $datum['month'] > $current_month
            ) {
                $rawData[$catType][$categoryTypeKey][$month][$currency]['per_month'] += (float) $perMonth;
            } else {
                $rawData[$catType][$categoryTypeKey][$month][$currency]['imaginary'] = (int) $datum['is_imaginary'];
            }
            $rawData[$catType][$categoryTypeKey]['total'][$currency]['per_month'] += (float) $perMonth;
        }

        $statDataIncome = [];
        $statDataExpense = [];
        $stat_data_saldo = [];

        $statDataIncome[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All income'),
                cashReportDdsService::ALL_INCOME_KEY,
                false,
                true,
                false,
                '',
                true
            ),
            $rawData[cashCategory::TYPE_INCOME][cashReportDdsService::ALL_INCOME_KEY] ?? []
        );
        $statDataExpense[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All expenses'),
                cashReportDdsService::ALL_EXPENSE_KEY,
                true,
                false,
                false,
                '',
                true
            ),
            $rawData[cashCategory::TYPE_EXPENSE][cashReportDdsService::ALL_EXPENSE_KEY] ?? []
        );

        $stat_data_saldo[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('Saldo'),
                cashReportDdsService::SALDO_KEY,
                false,
                false,
                true,
                '',
                true
            ),
            $this->getSaldo(
                $rawData[cashCategory::TYPE_INCOME][cashReportDdsService::ALL_INCOME_KEY] ?? [],
                $rawData[cashCategory::TYPE_EXPENSE][cashReportDdsService::ALL_EXPENSE_KEY] ?? []
            )
        );

        foreach ($this->accountRep->findAllActiveForContact() as $account) {
            $inc_data = $rawData[cashCategory::TYPE_INCOME][$account->getId()] ?? [];
            if ($inc_data) {
                $statDataIncome[] = new cashReportDdsStatDto(
                    new cashReportDdsEntity(
                        $account->getName(),
                        $account->getId(),
                        false,
                        true,
                        false,
                        $account->getIconHtml()
                    ),
                    $inc_data
                );
            }
            $exp_data = $rawData[cashCategory::TYPE_EXPENSE][$account->getId()] ?? [];
            if ($exp_data) {
                $statDataExpense[] = new cashReportDdsStatDto(
                    new cashReportDdsEntity(
                        $account->getName(),
                        $account->getId(),
                        true,
                        false,
                        false,
                        $account->getIconHtml()
                    ),
                    $exp_data
                );
            }
            if ($inc_data || $exp_data) {
                $stat_data_saldo[] = new cashReportDdsStatDto(
                    new cashReportDdsEntity(
                        $account->getName(),
                        $account->getId(),
                        false,
                        false,
                        true,
                        $account->getIconHtml()
                    ),
                    $this->getSaldo($inc_data, $exp_data)
                );
            }
        }

        return array_merge($statDataIncome, $statDataExpense, $stat_data_saldo) ?: [];
    }

    private function getSaldo($inc_data, $exp_data)
    {
        $saldo_data = [];
        $keys = array_keys($inc_data + $exp_data);
        foreach ($keys as $_key) {
            $currency_keys = array_keys(ifset($inc_data, $_key, []) + ifset($exp_data, $_key, []));
            foreach ($currency_keys as $_currency) {
                $saldo_data[$_key][$_currency] = [
                    'type' => cashReportDdsService::SALDO_KEY,
                    'per_month' => ifempty($inc_data, $_key, $_currency, 'per_month', 0) + ifempty($exp_data, $_key, $_currency, 'per_month', 0)
                ] + ifempty($inc_data, $_key, $_currency, []) + ifempty($exp_data, $_key, $_currency, []);
            }
        }

        return $saldo_data;
    }
}
