<?php

/**
 * Class cashRepeatTransactionRepeater
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
        $now = date('Y-m-d');
        $last_prolongation_date = cash()->getCache()->get('cash_last_prolongation_repeat_date');
        if ($last_prolongation_date && $last_prolongation_date <= $now) {
            return;
        }
        cash()->getCache()->set('cash_last_prolongation_repeat_date', $now, 3600*12);

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

                $startDate = new DateTime($date);
                if ($lastT) {
                    // но начать надо со следующей итерации, так как у нас уже есть "последняя" повторяющаяся транзакция
                    $startDate->modify(
                        sprintf('+%d %s', $repeatingTransaction->getRepeatingFrequency(), $repeatingTransaction->getRepeatingInterval())
                    );
            }

                cash()->getLogger()->debug(
                    sprintf(
                        'Trying to extend repeating transaction #%d starting from %s (%s)',
                        $repeatingTransaction->getId(),
                        $startDate->format('Y-m-d'),
                        json_encode(cash()->getHydrator()->extract($repeatingTransaction))
                    )
                );
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
