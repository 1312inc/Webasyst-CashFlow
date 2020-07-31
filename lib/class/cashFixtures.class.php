<?php

/**
 * Class cashFixtures
 */
class cashFixtures
{
    const RATE = 70;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var array
     */
    private $fixtures;

    /**
     * @var cashCategory[]
     */
    private $categories;

    /**
     * @var cashAccount
     */
    private $demoAccount;

    /**
     * @var cashRepeatingTransactionFactory
     */
    private $repeatingTxFactory;

    /**
     * @var cashTransactionFactory
     */
    private $txFactory;

    /**
     * @var cashEntityPersist
     */
    private $perister;

    /**
     * cashFixtures constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->currency = wa()->getLocale() === 'en_US' ? 'USD' : 'RUB';

        // names should be unique
        $this->fixtures = [
            cashCategory::TYPE_INCOME => array_reverse(
                [
                    'Sales' => [_w('Sales'), '#94fa4e'],
                    'Investment' => [_w('Investment'), '#78fa7a'],
                    'Loan' => [_w('Loan'), '#78faa2'],
                    'Cashback' => [_w('Cashback'), '#77fbfd'],
                    'Unexpected profit' => [_w('Unexpected profit'), '#81cafa'],
                ],
                true
            ),
            cashCategory::TYPE_EXPENSE => array_reverse(
                [
                    'Salary' => [_w('Salary'), '#e9382a'],
                    'Purchase' => [_w('Purchase'), '#d2483e'],
                    'Marketing' => [_w('Marketing'), '#d53964'],
                    'Delivery' => [_w('Delivery'), '#de6c92'],
                    'Rent' => [_w('Rent'), '#eebecf'],
                    'Errand' => [_w('Errand'), '#f7cebf'],
                    'Loan payout' => [_w('Loan payout'), '#f9dea2'],
                    'Commission' => [_w('Commission'), '#f2ab63'],
                    'Dividend' => [_w('Dividend'), '#e58231'],
                    'Refund' => [_w('Refund'), '#b75822'],
                    'Tax' => [_w('Tax'), '#a72e26'],
                    'Unexpected loss' => [_w('Unexpected loss'), '#f7cfd3'],
                ],
                true
            ),
        ];

        $this->repeatingTxFactory = cash()->getEntityFactory(cashRepeatingTransaction::class);
        $this->txFactory = cash()->getEntityFactory(cashTransaction::class);
        $this->perister = cash()->getEntityPersister();
    }

    /**
     * @throws waDbException
     * @throws waException
     */
    public function createAccountsAndCategories()
    {
        $this->perister->insert(
            (new cashAccount())
                ->setName(wa()->accountName())
                ->setCurrency($this->currency)
                ->setIcon('star')
        );

        foreach ($this->fixtures as $type => $categories) {
            foreach ($categories as $name => $data) {
                $this->categories[$name] = (new cashCategory())
                    ->setType($type)
                    ->setColor($data[1])
                    ->setName($data[0]);
                $this->perister->insert($this->categories[$name]);
            }
        }

        cash()->getModel(cashCategory::class)->insert(
            [
                'id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
                'type' => cashCategory::TYPE_TRANSFER,
                'color' => cashColorStorage::TRANSFER_CATEGORY_COLOR,
                'name' => _w('Transfers'),
                'create_datetime' => date('Y-m-d H:i:s'),
            ]
        );
    }

    public function createDemo()
    {
        try {
            $repeater = new cashTransactionRepeater();
            $repeatingDto = new cashRepeatingTransactionSettingsDto();
            $repeatingDto->interval = cashRepeatingTransaction::INTERVAL_MONTH;

            // счет "Demo account"
            $this->demoAccount = cash()->getEntityFactory(cashAccount::class)->createNew();
            $this->demoAccount
                ->setName(_w('Demo account'))
                ->setIcon('star');
            $this->perister->insert($this->demoAccount);

            // повторяющуюся транзакцию "аренда" ≈ 30К. начало — 1 год назад. конец — никогда. категория — Rent.
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-1 year');
            $tx
                ->setDescription('аренда')
                ->setAmount($this->getAmountInCurrency(50000))
                ->setDate($startDate->format('Y-m-d H:i:s'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Rent']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // повторяющуюся транзакцию "продажи" ≈ 100К. начало — 6 месяцев назад. конец — никогда. категория — Sales.
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-6 months');
            $tx
                ->setDescription('продажи')
                ->setAmount($this->getAmountInCurrency(100000))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Sales']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // повторяющуюся транзакцию "продажи" ≈ 50К. начало — 1 год назад. конец — 6 месяцев назад. категория — Sales.
            $repeatingDto->end_type = cashRepeatingTransaction::REPEATING_END_ONDATE;
            $repeatingDto->end['ondate'] = (new DateTime('-6 months'))->format('Y-m-d H:i:s');
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-1 year');
            $tx
                ->setDescription('продажи')
                ->setAmount($this->getAmountInCurrency(50000))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Sales']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // одиночную транзакцию "начальные инвестиции" = 50К. 1 год назад. категория — Investment.
            $tx = $this->txFactory->createNew();
            $startDate = new DateTime('-1 year');
            $tx
                ->setAmount($this->getAmountInCurrency(50000))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Investment'])
                ->setDescription('начальные инвестиции')
                ->setDate($startDate->format('Y-m-d'))
                ->setDatetime($startDate->format('Y-m-d H:i:s'));
            $this->perister->insert($tx);
        } catch (Exception $ex) {
            cash()->getLogger()->error('Demo init error', $ex);
        }
    }

    /**
     * @param float|int $amount
     *
     * @return float
     */
    private function getAmountInCurrency($amount): float
    {
        if ($this->currency === 'USD') {
            return round($amount / self::RATE, 2);
        }

        return (float)$amount;
    }
}