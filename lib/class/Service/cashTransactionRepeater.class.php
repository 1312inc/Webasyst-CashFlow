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
     * @param cashRepeatingTransaction $transaction
     * @param DateTime|null            $startDate
     *
     * @return bool|cashTransaction|null
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function repeat(cashRepeatingTransaction $transaction, DateTime $startDate = null)
    {
        $data = cash()->getHydrator()->extract($transaction);
        $endSettings = $transaction->getRepeatingEndConditions();
        unset($data['id'], $data['create_datetime'], $data['create_datetime'], $data['update_datetime']);
        $data['repeating_id'] = $transaction->getId();
        $startDate = $startDate ?: new DateTime($transaction->getDate());
        $t = null;

        switch ($transaction->getRepeatingEndType()) {
            case cashRepeatingTransaction::REPEATING_END_ONDATE:
                $endDate = new DateTime($endSettings['ondate']);
                while ($startDate <= $endDate) {
                    $t = $this->saveNextTransaction($transaction, $data, $startDate);
                }
                break;

            case cashRepeatingTransaction::REPEATING_END_AFTER:
                $counter = 0;
                while ($counter++ < $endSettings['after']) {
                    $t = $this->saveNextTransaction($transaction, $data, $startDate);
                }

                break;

            case cashRepeatingTransaction::REPEATING_END_NEVER:
                $counter = 0;
                while ($counter++ <= $this->getOccurrencesByDefault($transaction)) {
                    $t = $this->saveNextTransaction($transaction, $data, $startDate);
                }
                break;

            default:
                throw new kmwaRuntimeException('No repeating transaction end setting');
        }

        return $t;
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param array                    $data
     * @param DateTime                 $startDate
     *
     * @return bool|cashTransaction
     * @throws waException
     */
    private function saveNextTransaction(cashRepeatingTransaction $transaction, array $data, DateTime $startDate)
    {
        $data['date'] = $startDate->modify(
            sprintf('+%d %s', $transaction->getRepeatingFrequency(), $transaction->getRepeatingInterval())
        )->format('Y-m-d H:i:s');

        return $this->transactionSaver->saveFromArray($this->transactionFactory->createNew(), $data);
    }

    /**
     * @param cashRepeatingTransaction $transaction
     *
     * @return int
     */
    private function getOccurrencesByDefault(cashRepeatingTransaction $transaction)
    {
        switch ($transaction->getRepeatingInterval()) {
            case cashRepeatingTransaction::INTERVAL_DAY:
                return 365;

            case cashRepeatingTransaction::INTERVAL_WEEK:
                return 60;

            case cashRepeatingTransaction::INTERVAL_MONTH:
                return 12;

            case cashRepeatingTransaction::INTERVAL_YEAR:
                return 10;
        }
    }
}
