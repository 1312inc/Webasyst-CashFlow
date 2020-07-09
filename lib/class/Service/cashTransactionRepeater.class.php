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
     * @return bool|cashTransaction[]|null
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function repeat(cashRepeatingTransaction $repeatingTransaction, DateTime $startDate = null)
    {
        $data = cash()->getHydrator()->extract($repeatingTransaction);
        $endSettings = $repeatingTransaction->getRepeatingEndConditions();
        unset($data['id'], $data['create_datetime'], $data['create_datetime'], $data['update_datetime']);
        $data['repeating_id'] = $repeatingTransaction->getId();
        $startDate = $startDate ?: new DateTime($repeatingTransaction->getDate());
        $t = [];

        switch ($repeatingTransaction->getRepeatingEndType()) {
            case cashRepeatingTransaction::REPEATING_END_ONDATE:
                $endDate = new DateTime($endSettings['ondate']);
                while ($startDate <= $endDate) {
                    $t[] = $this->createNextTransaction($repeatingTransaction, $data, $startDate);
                }
                break;

            case cashRepeatingTransaction::REPEATING_END_AFTER:
                $counter = 0;
                while ($counter++ < $endSettings['after']) {
                    $t[] = $this->createNextTransaction($repeatingTransaction, $data, $startDate);
                }

                break;

            case cashRepeatingTransaction::REPEATING_END_NEVER:
                $endDate = $this->getEndDateForNeverEndByDefault($repeatingTransaction, $startDate);
                while ($startDate <= $endDate) {
                    $t[] = $this->createNextTransaction($repeatingTransaction, $data, $startDate);
                }
                break;

            default:
                throw new kmwaRuntimeException('No repeating transaction end setting');
        }

        if ($t) {
            $this->transactionSaver->persistTransactions($t);
        }

        return $t;
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param array                    $data
     * @param DateTime                 $startDate
     *
     * @return cashTransaction
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws waException
     */
    private function createNextTransaction(cashRepeatingTransaction $transaction, array $data, DateTime $startDate)
    {
        $data['date'] = $startDate->format('Y-m-d H:i:s');
        $t = $this->transactionSaver->populateFromArray(
            $this->transactionFactory->createNew(),
            $data,
            new cashTransactionSaveParamsDto()
        );

        $startDate->modify(sprintf('+%d %s', $transaction->getRepeatingFrequency(), $transaction->getRepeatingInterval()));

        return $t;
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param DateTime                 $startDate
     *
     * @return DateTime
     */
    private function getEndDateForNeverEndByDefault(cashRepeatingTransaction $transaction, DateTime $startDate)
    {
        $date = max(new DateTime(), clone $startDate);
        switch ($transaction->getRepeatingInterval()) {
            case cashRepeatingTransaction::INTERVAL_DAY:
                $date->modify('+1 year');
                break;

            case cashRepeatingTransaction::INTERVAL_WEEK:
                $date->modify('+60 weeks');
                break;

            case cashRepeatingTransaction::INTERVAL_MONTH:
                $date->modify('+12 months');
                break;

            case cashRepeatingTransaction::INTERVAL_YEAR:
                $date->modify('+10 years');
                break;
        }

        return $date;
    }
}
