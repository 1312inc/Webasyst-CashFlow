<?php

/**
 * Class cashTransactionExternalEntityFactory
 */
class cashTransactionExternalEntityFactory
{
    /**
     * @param cashTransaction $transaction
     *
     * @return cashTransactionExternalEntityInterface
     * @throws waException
     */
    static public function createFromTransaction(cashTransaction $transaction)
    {
        $source = $transaction->getExternalSource();
        switch ($source) {
            case 'shop':
                return new cashTransactionExternalEntityShopOrder($transaction);
        }

        return null;
    }
}
