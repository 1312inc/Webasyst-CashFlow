<?php

/**
 * Class cashBackendImportListener
 */
class cashRepeatTransactionRepeater extends waEventHandler
{
    /**
     * @param cashEvent $event
     *
     * @return void
     * @throws waException
     */
    public function afterTransactionPagePreExecute($event)
    {
        /** @var DateTime $endDate */
        $endDate = $event->getObject();

        $repeatingTransactions = cash()->getEntityRepository(cashRepeatingTransaction::class)->findNeverEndingAfterDate($endDate);
        if (!$repeatingTransactions) {
            return;
        }

        /** @var cashTransactionRepository $transRep */
        $transRep = cash()->getEntityRepository(cashTransaction::class);
        $repeater = new cashTransactionRepeater();

        foreach ($repeatingTransactions as $repeatingTransaction) {
            try {
                $lastT = $transRep->findLastByRepeatingId($repeatingTransaction->getId());
                $date = $lastT instanceof cashTransaction
                    ? $lastT->getDate()
                    : $repeatingTransaction->getDataField('last_transaction_date');
                if (!$date) {
                    $date = $repeatingTransaction->getDate();
                }

                cash()->getLogger()->debug(
                    sprintf('Trying to extend repeating transaction #%d starting from %s', $repeatingTransaction->getId(), $date)
                );

                $startDate = new DateTime($date);
                if ($lastT) {
                    // но начать надо со следующей итерации, так как у нас уже есть "последняя" повторяющаяся транзакция
                    $startDate->modify(
                        sprintf('+%d %s', $repeatingTransaction->getRepeatingFrequency(), $repeatingTransaction->getRepeatingInterval())
                    );
                }

                $repeater->repeat($repeatingTransaction, $startDate);
            } catch (Exception $ex) {
                cash()->getLogger()->error(
                    sprintf('Can`t extend repeating transaction #%d', $repeatingTransaction->getId()),
                    $ex
                );
            }
        }
    }
}
