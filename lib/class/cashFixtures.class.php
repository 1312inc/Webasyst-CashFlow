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
                    'Sales' => [_wd(cashConfig::APP_ID, 'Sales'), '#00CC66', false],
                    'Investment' => [_wd(cashConfig::APP_ID, 'Investment'), '#00CCCC', false],
                    'Cashback' => [_wd(cashConfig::APP_ID, 'Cashback'), '#00CCFF', false],
                    'Took a loan' => [_wd(cashConfig::APP_ID, 'Took a loan'), '#6699FF', false],
                    'Unexpected profit' => [_wd(cashConfig::APP_ID, 'Unexpected profit'), '#6666FF', false],
                ],
                true
            ),
            cashCategory::TYPE_EXPENSE => array_reverse(
                [
                    'Office rent' => [_wd(cashConfig::APP_ID, 'Office rent'), '#880E4F', false],
                    'Salaries' => [_wd(cashConfig::APP_ID, 'Salaries'), '#E91E63', false],
                    'Purchasing & supply' => [_wd(cashConfig::APP_ID, 'Purchasing & supply'), '#E91E63', false],
                    'Shipping' => [_wd(cashConfig::APP_ID, 'Shipping'), '#EC407A', false],
                    'Marketing' => [_wd(cashConfig::APP_ID, 'Marketing'), '#F48FB1', false],
                    'Hosting' => [_wd(cashConfig::APP_ID, 'Hosting'), '#F8BBD0', false],
                    'Taxes' => [_wd(cashConfig::APP_ID, 'Taxes'), '#FF8A65', false],
                    'Commissions & fees' => [_wd(cashConfig::APP_ID, 'Commissions & fees'), '#FB8C20', false],
                    'Refunds' => [_wd(cashConfig::APP_ID, 'Refunds'), '#FF1312', false],
                    'Loan payouts' => [_wd(cashConfig::APP_ID, 'Loan payouts'), '#EE2222', false],
                    'Unexpected loss' => [_wd(cashConfig::APP_ID, 'Unexpected loss'), '#FF1312', false],
                    'Dividend payouts' => [_wd(cashConfig::APP_ID, 'Dividend payouts'), '#2EA2FD', true],
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
            $sort = 0;
            foreach ($categories as $name => $data) {
                $this->categories[$name] = (new cashCategory())
                    ->setType($type)
                    ->setColor($data[1])
                    ->setName($data[0])
                    ->setIsProfit((int) $data[2])
                    ->setSort($sort++);
                $this->perister->insert($this->categories[$name]);
            }
        }

        $transferCategory = cash()->getEntityFactory(cashCategory::class)->createNewTransferCategory();
        $transferCategory->setSort(1312);
        $data = cash()->getHydrator()->extract($transferCategory);
        cash()->getModel(cashCategory::class)->insert($data);

        $incomeNoCategory = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryIncome();
        $transferCategory->setSort(1313);
        $data = cash()->getHydrator()->extract($incomeNoCategory);
        cash()->getModel(cashCategory::class)->insert($data);

        $expenseNoCategory = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryExpense();
        $transferCategory->setSort(1314);
        $data = cash()->getHydrator()->extract($expenseNoCategory);
        cash()->getModel(cashCategory::class)->insert($data);
    }

    public function createDemo()
    {
        try {
            $repeater = new cashTransactionRepeater();
            $repeatingDto = new cashRepeatingTransactionSettingsDto();
            $repeatingDto->interval = cashRepeatingTransaction::INTERVAL_MONTH;

            // an isolated demo account!
            $this->demoAccount = cash()->getEntityFactory(cashAccount::class)->createNew();
            $this->demoAccount
                ->setName(_wd(cashConfig::APP_ID, 'Demo account'))
                ->setCurrency($this->currency)
                ->setSort(0)
                ->setIcon('luggage');
            $this->perister->insert($this->demoAccount);


            // a huge initial investment transaction!
            $tx = $this->txFactory->createNew();
            $startDate = new DateTime('-11 month');
            $tx
                ->setAmount($this->getAmountInCurrency(293041))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Investment'])
                ->setDescription( _wd(cashConfig::APP_ID, 'Initial investment') )
                ->setDate($startDate->format('Y-m-d'))
                ->setDatetime($startDate->format('Y-m-d H:i:s'));
            $this->perister->insert($tx);

            $tx = $this->txFactory->createNew();
            $startDate = new DateTime('-11 month');
            $tx
                ->setAmount($this->getAmountInCurrency(192000))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Took a loan'])
                ->setDescription( _wd(cashConfig::APP_ID, 'Took a loan in the bank') )
                ->setDate($startDate->format('Y-m-d'))
                ->setDatetime($startDate->format('Y-m-d H:i:s'));
            $this->perister->insert($tx);

            /* NEVER-ENDING REPEATING TRANSACTIONS */

            // bloody office rent :()
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-10 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Office rent') )
                ->setAmount($this->getAmountInCurrency(27500))
                ->setDate($startDate->format('Y-m-d H:i:s'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Office rent']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // oh that marketing again
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-6 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Marketing') )
                ->setAmount($this->getAmountInCurrency(37000))
                ->setDate($startDate->format('Y-m-d H:i:s'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Marketing']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // endless product purchases and purchases and purchases
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-11 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Product purchase') )
                ->setAmount($this->getAmountInCurrency(54700))
                ->setDate($startDate->format('Y-m-d H:i:s'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Purchasing & supply']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // oh, and salaries!
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-3 months');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Sales team') )
                ->setAmount($this->getAmountInCurrency(120000))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Salaries']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // oh that marketing again
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('+2 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Dividend payouts') )
                ->setAmount($this->getAmountInCurrency(50000))
                ->setDate($startDate->format('Y-m-d H:i:s'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Dividend payouts']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // ok, we've started selling! hooray!
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-3 months');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Sales') )
                ->setAmount($this->getAmountInCurrency(350000))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Sales']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);


            // an unexpected sale :)
            $tx = $this->txFactory->createNew();
            $startDate = new DateTime('-4 month');
            $tx
                ->setAmount($this->getAmountInCurrency(119511))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Unexpected profit'])
                ->setDescription( _wd(cashConfig::APP_ID, 'Pop-up store time-limited sale') )
                ->setDate($startDate->format('Y-m-d'))
                ->setDatetime($startDate->format('Y-m-d H:i:s'));
            $this->perister->insert($tx);

            /* TIME-LIMITED REPEATING TRANSACTIONS */

            // salary
            $repeatingDto->end_type = cashRepeatingTransaction::REPEATING_END_ONDATE;
            $repeatingDto->end['ondate'] = (new DateTime('-4 months'))->format('Y-m-d H:i:s');
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-10 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Sales team') )
                ->setAmount($this->getAmountInCurrency(69300))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Salaries']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // sales
            $repeatingDto->end_type = cashRepeatingTransaction::REPEATING_END_ONDATE;
            $repeatingDto->end['ondate'] = (new DateTime('-4 months'))->format('Y-m-d H:i:s');
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-11 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Sales') )
                ->setAmount($this->getAmountInCurrency(110000))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Sales']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);

            // loan payout
            $repeatingDto->end_type = cashRepeatingTransaction::REPEATING_END_ONDATE;
            $repeatingDto->end['ondate'] = (new DateTime('+2 months'))->format('Y-m-d H:i:s');
            $tx = $this->repeatingTxFactory->createFromTransactionWithRepeatingSettings(
                $this->txFactory->createNew(),
                $repeatingDto
            );
            $startDate = new DateTime('-10 month');
            $tx
                ->setDescription( _wd(cashConfig::APP_ID, 'Loan payout') )
                ->setAmount($this->getAmountInCurrency(23055))
                ->setDate($startDate->format('Y-m-d'))
                ->setDateTime($startDate->format('Y-m-d H:i:s'))
                ->setAccount($this->demoAccount)
                ->setCategory($this->categories['Loan payouts']);
            $this->perister->insert($tx);
            $repeater->repeat($tx);


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
