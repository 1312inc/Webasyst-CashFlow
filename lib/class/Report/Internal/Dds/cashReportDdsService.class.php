<?php

final class cashReportDdsService
{
    public const TYPE_CATEGORY   = 'category';
    public const TYPE_CONTRACTOR = 'contractor';
    public const TYPE_ACCOUNT    = 'account';

    public const ALL_INCOME_KEY  = 'all_income';
    public const ALL_EXPENSE_KEY = 'all_expense';
    public const SALDO_KEY = 'saldo';

    /**
     * @return cashReportDdsStatDto[]
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function getDataForTypeAndPeriod(cashReportDdsTypeDto $type, cashReportPeriod $period): array
    {
        switch ($type->id) {
            case self::TYPE_CATEGORY:
                $data = (new cashReportDdsCategoryDataProvider())->getDataForPeriod($period);

                break;

            case self::TYPE_ACCOUNT:
                $data = (new cashReportDdsAccountDataProvider())->getDataForPeriod($period);

                break;

            case self::TYPE_CONTRACTOR:
                $data = (new cashReportDdsContractorDataProvider())->getDataForPeriod($period);

                break;

            default:
                throw new kmwaRuntimeException(sprintf('Wrong dds report type %s', $type->id));
        }

        return $data;
    }

    public function formatDataForPie(array $data, cashReportDdsTypeDto $type, cashReportPeriod $period): array
    {
        $chartData = [
            self::ALL_INCOME_KEY => [],
            self::ALL_EXPENSE_KEY => [],
        ];

        /** @var cashReportDdsStatDto $datum */
        foreach ($data as $datum) {
            $id = $datum->entity->getId();
            if (in_array(
                $id,
                [self::ALL_INCOME_KEY, self::ALL_EXPENSE_KEY, cashCategoryFactory::TRANSFER_CATEGORY_ID]
            )) {
                continue;
            }

            $incOrExp = $datum->entity->isExpense() ? self::ALL_EXPENSE_KEY : self::ALL_INCOME_KEY;
            /** @var cashCurrencyVO $currency */
            foreach ($datum->currencies as $currency) {
                $currencyCode = $currency->getCode();
                if (!isset($chartData[$incOrExp][$currencyCode])) {
                    $chartData[$incOrExp][$currencyCode] = new cashReportDdsPieDto($currency);
                }
                $chartData[$incOrExp][$currencyCode]->columns[$id] = [$datum->entity->getName()];
                if ($datum->entity->getColor()) {
                    $chartData[$incOrExp][$currencyCode]->colors[$datum->entity->getName()] = [
                        $datum->entity->getColor(),
                    ];
                }
            }

            foreach ($datum->valuesPerPeriods as $valuesPerPeriod) {
                foreach ($valuesPerPeriod as $currency => $value) {
                    /** @var cashReportDdsPieDto $statIncomeOrExpenseForCurrency */
                    $statIncomeOrExpenseForCurrency = $chartData[$incOrExp][$currency];

                    $statIncomeOrExpenseForCurrency->columns[$id][] = (float) abs($value['per_month']);

                    if (!isset($statIncomeOrExpenseForCurrency->total[$id])) {
                        $statIncomeOrExpenseForCurrency->total[$id] = 0;
                    }
                    $statIncomeOrExpenseForCurrency->total[$id] += (float) abs($value['per_month']);
                }
            }
        }

        return $chartData;
    }

    /**
     * @return cashReportDdsTypeDto[]
     */
    public function getTypes(): array
    {
        return [
            self::TYPE_CATEGORY => new cashReportDdsTypeDto(self::TYPE_CATEGORY, _w('Categories'), true),
            self::TYPE_ACCOUNT => new cashReportDdsTypeDto(self::TYPE_ACCOUNT, _w('Accounts'), true),
//            self::TYPE_CONTRACTOR => new cashReportDdsServiceTypeDto(self::TYPE_CONTRACTOR, _w('Contractors'), true),
        ];
    }
}
