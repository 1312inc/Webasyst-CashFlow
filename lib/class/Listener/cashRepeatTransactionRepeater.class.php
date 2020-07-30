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

        $trans = cash()->getEntityRepository(cashRepeatingTransaction::class)->findNeverEndingAfterDate($endDate);
        if (!$trans) {
            return;
        }

        /** @var cashTransactionRepository $transRep */
        $transRep = cash()->getEntityRepository(cashTransaction::class);
        $repeater = new cashTransactionRepeater();

        foreach ($trans as $transaction) {
            try {
                cash()->getLogger()->debug(
                    sprintf(
                        'Trying to extend repeating transaction #%d starting from %s',
                        $transaction->getId(),
                        $transaction->getDataField('last_transaction_date')
                    )
                );

                $lastT = $transRep->findLastByRepeatingId($transaction->getId());
                $date = $lastT instanceof cashTransaction ? $lastT->getDate() : $transaction->getDataField('last_transaction_date');

                if (!$date) {
                    $date = $transaction->getDate();
                }
                $startDate = new DateTime($date);
                if ($lastT) {
                    // но начать надо со следующей итерации, так как у нас уже есть "последняя" повторяющаяся транзакция
                    $startDate->modify(
                        sprintf('+%d %s', $transaction->getRepeatingFrequency(), $transaction->getRepeatingInterval())
                    );
                }

                $repeater->repeat($transaction, $startDate);
            } catch (Exception $ex) {
                cash()->getLogger()->error(
                    sprintf('Can`t extend repeating transaction #%d', $transaction->getId()),
                    $ex
                );
            }
        }
    }
}
