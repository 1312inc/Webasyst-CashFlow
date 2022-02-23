<?php

/**
 * Class cashTransactionRepeater
 */
final class cashTransactionRepeater
{
    /**
     * @var cashTransactionSaver
     */
    private $transactionSaver;

    /**
     * @var cashTransactionFactory
     */
    private $transactionFactory;

    /**
     * cashTransactionRepeater constructor.
     */
    public function __construct()
    {
        $this->transactionSaver = new cashTransactionSaver();
        $this->transactionFactory = new cashTransactionFactory();
    }

    /**
     * @param cashRepeatingTransaction $repeatingTransaction
     * @param DateTime|null            $startDate
     *
     * @return bool|array<int>|null
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function repeat(
        cashRepeatingTransaction $repeatingTransaction,
        DateTime $startDate = null,
        bool $startWithNext = false
    ): array {
        $data = cash()->getHydrator()->extract($repeatingTransaction);
        $endSettings = $repeatingTransaction->getRepeatingEndConditions();
        unset($data['id'], $data['create_datetime'], $data['create_datetime'], $data['update_datetime']);
        $data['repeating_id'] = $repeatingTransaction->getId();

        if ($startDate) {
            $this->modifyStartDate($startDate, $repeatingTransaction);
        }
        $startDate = $startDate ?: new DateTime($repeatingTransaction->getDate());

        $tIds = [];

        switch ($repeatingTransaction->getRepeatingEndType()) {
            case cashRepeatingTransaction::REPEATING_END_ONDATE:
                $endDate = new DateTime($endSettings['ondate']);
                cash()->getLogger()->debug(
                    sprintf(
                        'Repeat %s transaction until %s',
                        $repeatingTransaction->getRepeatingEndType(),
                        $endDate->format('Y-m-d')
                    )
                );

                while ($startDate <= $endDate) {
                    $ids = $this->createRepeating($repeatingTransaction, $data, $startDate);
                    if (is_array($ids)) {
                        array_map(
                            static function (cashTransaction $t) use (&$tIds) {
                                $tIds[] = $t->getId();
                            },
                            $ids
                        );
                    }
                }
                break;

            case cashRepeatingTransaction::REPEATING_END_AFTER:
                $counter = 0;
                cash()->getLogger()->debug(
                    sprintf(
                        'Repeat %s transaction until %s',
                        $repeatingTransaction->getRepeatingEndType(),
                        $endSettings['after']
                    )
                );

                while ($counter++ < $endSettings['after']) {
                    $ids = $this->createRepeating($repeatingTransaction, $data, $startDate);
                    if (is_array($ids)) {
                        array_map(
                            static function (cashTransaction $t) use (&$tIds) {
                                $tIds[] = $t->getId();
                            },
                            $ids
                        );
                    }
                }
                break;

            case cashRepeatingTransaction::REPEATING_END_NEVER:
                $endDate = $this->getEndDateForNeverEndByDefault($repeatingTransaction, $startDate);
                cash()->getLogger()->debug(
                    sprintf(
                        'Repeat %s transaction until %s',
                        $repeatingTransaction->getRepeatingEndType(),
                        $endDate->format('Y-m-d')
                    )
                );

                while ($startDate <= $endDate) {
                    $ids = $this->createRepeating($repeatingTransaction, $data, $startDate);
                    if (is_array($ids)) {
                        array_map(
                            static function (cashTransaction $t) use (&$tIds) {
                                $tIds[] = $t->getId();
                            },
                            $ids
                        );
                    }
                }
                break;

            default:
                throw new kmwaRuntimeException('No repeating transaction end setting');
        }

        $ids = $this->flushTransaction(true);
        if ($ids) {
            array_map(
                static function (cashTransaction $t) use (&$tIds) {
                    $tIds[] = $t->getId();
                },
                $ids
            );
        }

        return $tIds;
    }

    private function createRepeating(
        cashRepeatingTransaction $repeatingTransaction,
        array $data,
        DateTime $startDate
    ): ?array {
        $newT = $this->createNextTransaction($repeatingTransaction, $data, $startDate);
        if ($newT) {
            $this->transactionSaver->addToPersist($newT);

            return $this->flushTransaction();
        }

        return null;
    }

    private function flushTransaction(bool $force = false): ?array
    {
        if (count($this->transactionSaver->getToPersist()) % 100 === 0 || $force) {
            return $this->transactionSaver->persistTransactions();
        }

        return null;
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param array                    $data
     * @param DateTime                 $startDate
     *
     * @return cashTransaction|null
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws waException
     */
    private function createNextTransaction(
        cashRepeatingTransaction $transaction,
        array $data,
        DateTime $startDate
    ): ?cashTransaction {
        $data['date'] = $startDate->format('Y-m-d H:i:s');
        $t = $this->transactionSaver->populateFromArray(
            $this->transactionFactory->createNew(),
            $data,
            new cashTransactionSaveParamsDto()
        );

        if (!$t) {
            return null;
        }

        $this->modifyStartDate($startDate, $transaction);

        return $t;
    }

    private function modifyStartDate(DateTime $startDate, cashRepeatingTransaction $transaction): void
    {
        if ($transaction->getRepeatingInterval() === cashRepeatingTransaction::INTERVAL_MONTH) {
            cashDatetimeHelper::addMonthToDate(
                $startDate,
                $transaction->getRepeatingFrequency(),
                new DateTime($transaction->getDate())
            );
        } else {
            $startDate->modify(
                sprintf('+%d %s', $transaction->getRepeatingFrequency(), $transaction->getRepeatingInterval())
            );
        }
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param DateTime                 $startDate
     *
     * @return DateTime
     */
    private function getEndDateForNeverEndByDefault(
        cashRepeatingTransaction $transaction,
        DateTime $startDate
    ): DateTime {
        $date = max(new DateTime(), clone $startDate);
        switch ($transaction->getRepeatingInterval()) {
            case cashRepeatingTransaction::INTERVAL_DAY:
                $date->modify('+3 year');
                break;

            case cashRepeatingTransaction::INTERVAL_WEEK:
                $date->modify('+160 weeks');
                break;

            case cashRepeatingTransaction::INTERVAL_MONTH:
                $date->modify('+36 months');
                break;

            case cashRepeatingTransaction::INTERVAL_YEAR:
                $date->modify('+10 years');
                break;

            default:
                throw new kmwaRuntimeException('No repeating transaction interval setting');
        }

        return $date;
    }
}
